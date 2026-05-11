<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'latestMessage'])->orderByDesc('created_at');

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->priority && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('ticket_number', 'like', "%{$request->search}%")
                  ->orWhere('subject', 'like', "%{$request->search}%")
                  ->orWhereHas('user', function ($q2) use ($request) {
                      $q2->where('full_name', 'like', "%{$request->search}%")
                         ->orWhere('email', 'like', "%{$request->search}%");
                  });
            });
        }

        $tickets = $query->paginate(20);

        return view('admin.tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['user', 'messages', 'assignedAgent']);

        $ticket->messages()
            ->where('sender_type', 'user')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $ticket->update(['unread_admin' => 0]);

        return view('admin.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $request->validate([
            'body'       => 'required|string|max:10000',
            'attachment' => 'nullable|file|max:20480|mimes:jpg,jpeg,png,gif,webp,pdf,xml,zip,txt',
        ]);

        $data = [
            'ticket_id'   => $ticket->id,
            'sender_type' => 'admin',
            'sender_id'   => auth()->id(),
            'body'        => $request->body,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('ticket-attachments/' . $ticket->id, 'public');
            $data['attachment_path'] = $path;
            $data['attachment_name'] = $file->getClientOriginalName();
            $data['attachment_mime'] = $file->getMimeType();
        }

        TicketMessage::create($data);
        $ticket->increment('unread_user');
        $ticket->update(['status' => 'pending', 'updated_at' => now()]);

        return redirect()->route('admin.tickets.show', $ticket)->with('success', 'Reply sent.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate(['status' => 'required|in:open,pending,closed']);
        $ticket->update([
            'status'    => $request->status,
            'closed_at' => $request->status === 'closed' ? now() : null,
        ]);
        return response()->json(['status' => $request->status]);
    }

    public function updatePriority(Request $request, Ticket $ticket)
    {
        $request->validate(['priority' => 'required|in:low,medium,high,urgent']);
        $ticket->update(['priority' => $request->priority]);
        return response()->json(['priority' => $request->priority]);
    }

    public function ticketUsers(Request $request)
    {
        $query = TicketUser::withCount('tickets')->orderByDesc('created_at');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        $users = $query->paginate(20);
        return view('admin.ticket-users.index', compact('users'));
    }
}
