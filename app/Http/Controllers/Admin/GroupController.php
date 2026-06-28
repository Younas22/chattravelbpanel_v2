<?php

namespace App\Http\Controllers\Admin;

use App\Events\GroupMessageSent;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\TicketUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $query = Group::withCount('members')->with('latestMessage')->orderByDesc('updated_at');

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $groups = $query->paginate(20);
        $allUsers = TicketUser::orderBy('full_name')->get();

        return view('admin.groups.index', compact('groups', 'allUsers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:150',
            'description'   => 'nullable|string|max:500',
            'member_ids'    => 'nullable|array',
            'member_ids.*'  => 'exists:ticket_users,id',
        ]);

        $group = Group::create([
            'name'        => $request->name,
            'description' => $request->description,
            'created_by'  => auth()->id(),
        ]);

        if ($request->member_ids) {
            $sync = [];
            foreach ($request->member_ids as $id) {
                $sync[$id] = ['last_read_at' => now()];
            }
            $group->members()->attach($sync);
        }

        return response()->json(['redirect_url' => route('admin.groups.show', $group)]);
    }

    public function show(Group $group)
    {
        $group->load(['members', 'messages']);
        $group->update(['unread_admin' => 0]);

        $availableUsers = TicketUser::whereNotIn('id', $group->members->pluck('id'))
            ->orderBy('full_name')
            ->get();

        $groups = Group::withCount('members')->with('latestMessage')->orderByDesc('updated_at')->get();

        return view('admin.groups.show', compact('group', 'availableUsers', 'groups'));
    }

    public function sendMessage(Request $request, Group $group)
    {
        $request->validate([
            'body'       => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:20480|mimes:jpg,jpeg,png,gif,webp,pdf,xml,zip,mp4,txt',
        ]);

        if (!$request->body && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Message or attachment required.'], 422);
        }

        $data = [
            'group_id'    => $group->id,
            'sender_type' => 'admin',
            'sender_id'   => auth()->id(),
            'body'        => $request->body,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('group-' . $group->id, 'public_direct');
            $data['attachment_path'] = $path;
            $data['attachment_name'] = $file->getClientOriginalName();
            $data['attachment_mime'] = $file->getMimeType();
            $data['attachment_size'] = $file->getSize();
            $data['attachment_type'] = $this->getAttachmentType($file->getMimeType());
        }

        $message = GroupMessage::create($data);
        $group->touch();

        try {
            broadcast(new GroupMessageSent($message))->toOthers();
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message'        => $message,
            'attachment_url' => $message->attachment_url,
        ]);
    }

    public function pollMessages(Request $request, Group $group)
    {
        $afterId = $request->integer('after_id', 0);

        $messages = $group->messages()
            ->when($afterId, fn($q) => $q->where('id', '>', $afterId))
            ->get()
            ->map(fn($m) => [
                'id'              => $m->id,
                'sender_type'     => $m->sender_type,
                'sender_name'     => $m->sender_name,
                'sender_avatar'   => $m->sender_avatar,
                'body'            => $m->body,
                'attachment_url'  => $m->attachment_url,
                'attachment_name' => $m->attachment_name,
                'attachment_type' => $m->attachment_type,
                'created_at'      => $m->created_at->toISOString(),
            ]);

        if ($messages->isNotEmpty()) {
            $group->update(['unread_admin' => 0]);
        }

        return response()->json(['messages' => $messages]);
    }

    public function addMember(Request $request, Group $group)
    {
        $request->validate([
            'ticket_user_id' => 'required|exists:ticket_users,id',
        ]);

        $group->members()->syncWithoutDetaching([
            $request->ticket_user_id => ['last_read_at' => now()],
        ]);

        return back()->with('success', 'Member added.');
    }

    public function removeMember(Group $group, TicketUser $ticketUser)
    {
        $group->members()->detach($ticketUser->id);

        return back()->with('success', 'Member removed.');
    }

    public function destroy(Group $group)
    {
        $group->messages()->whereNotNull('attachment_path')->each(function ($msg) {
            Storage::disk('public_direct')->delete($msg->attachment_path);
        });
        $group->delete();

        return response()->json(['deleted' => true]);
    }

    private function getAttachmentType(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        if (in_array($mime, ['application/zip', 'application/x-rar-compressed'])) return 'archive';
        return 'document';
    }
}
