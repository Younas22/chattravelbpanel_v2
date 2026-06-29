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

        if ($request->wantsJson()) {
            return response()->json([
                'groups' => $groups->getCollection()->map(fn($g) => [
                    'id'                => $g->id,
                    'name'              => $g->name,
                    'description'       => $g->description,
                    'profile_image_url' => $g->profileImageUrl(),
                    'members_count'     => $g->members_count,
                    'unread_admin'      => $g->unread_admin,
                    'last_message'      => $g->latestMessage?->body,
                    'updated_at'        => $g->updated_at->toISOString(),
                ]),
                'pagination' => [
                    'current_page' => $groups->currentPage(),
                    'last_page'    => $groups->lastPage(),
                    'total'        => $groups->total(),
                ],
            ]);
        }

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

    public function show(Request $request, Group $group)
    {
        $group->load(['members', 'messages.replyTo']);
        $group->update(['unread_admin' => 0]);

        if ($request->wantsJson()) {
            return response()->json([
                'group' => [
                    'id'                => $group->id,
                    'name'              => $group->name,
                    'description'       => $group->description,
                    'profile_image_url' => $group->profileImageUrl(),
                ],
                'members' => $group->members->map(fn($m) => [
                    'id'                => $m->id,
                    'full_name'         => $m->full_name,
                    'email'             => $m->email,
                    'profile_image_url' => $m->profileImageUrl(),
                ]),
                'messages' => $group->messages->map(fn($m) => $m->toApiArray()),
            ]);
        }

        $availableUsers = TicketUser::whereNotIn('id', $group->members->pluck('id'))
            ->orderBy('full_name')
            ->get();

        $groups = Group::withCount('members')->with('latestMessage')->orderByDesc('updated_at')->get();

        return view('admin.groups.show', compact('group', 'availableUsers', 'groups'));
    }

    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
        ]);

        $group->update([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true]);
    }

    public function updateImage(Request $request, Group $group)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        if ($group->profile_image) {
            Storage::disk('public_direct')->delete($group->profile_image);
        }

        $path = $request->file('image')->store('group-avatars', 'public_direct');
        $group->update(['profile_image' => $path]);

        return response()->json(['success' => true]);
    }

    public function sendMessage(Request $request, Group $group)
    {
        $request->validate([
            'body'            => 'nullable|string|max:5000',
            'attachment'      => 'nullable|file|max:20480|mimes:jpg,jpeg,png,gif,webp,pdf,xml,zip,mp4,txt',
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        if (!$request->body && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Message or attachment required.'], 422);
        }

        // Idempotency: a retried send (e.g. the desktop app's offline queue
        // replaying after a dropped connection) reuses the same key, so a
        // request that actually went through but lost its response returns
        // the original message here instead of creating a duplicate.
        if ($request->idempotency_key) {
            $existing = GroupMessage::where('idempotency_key', $request->idempotency_key)->first();
            if ($existing) {
                return response()->json([
                    'message'        => $existing,
                    'attachment_url' => $existing->attachment_url,
                ]);
            }
        }

        $data = [
            'group_id'        => $group->id,
            'reply_to_id'     => $request->integer('reply_to_id') ?: null,
            'sender_type'     => 'admin',
            'sender_id'       => auth()->id(),
            'body'            => $request->body,
            'idempotency_key' => $request->idempotency_key,
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
            ->with('replyTo')
            ->when($afterId, fn($q) => $q->where('id', '>', $afterId))
            ->get()
            ->map(fn($m) => $m->toApiArray());

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

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Member added.');
    }

    public function removeMember(Request $request, Group $group, TicketUser $ticketUser)
    {
        $group->members()->detach($ticketUser->id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

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
