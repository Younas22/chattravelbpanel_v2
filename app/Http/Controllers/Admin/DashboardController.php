<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Ticket;
use App\Models\Visitor;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'active_visitors'    => Visitor::online()->count(),
            'active_chats'       => Conversation::whereIn('status', ['active', 'pending'])->count(),
            'unread_messages'    => Conversation::sum('unread_admin'),
            'open_tickets'       => Ticket::where('status', 'open')->count(),
            'total_conversations' => Conversation::count(),
            'today_conversations' => Conversation::whereDate('created_at', today())->count(),
            'today_tickets'      => Ticket::whereDate('created_at', today())->count(),
            'closed_today'       => Conversation::whereDate('closed_at', today())->count(),
        ];

        $recentConversations = Conversation::with(['visitor', 'latestMessage'])
            ->open()
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        $recentTickets = Ticket::with('user')
            ->whereIn('status', ['open', 'pending'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $liveVisitors = Visitor::online()
            ->orderByDesc('last_activity_at')
            ->limit(10)
            ->get();

        $weeklyChats = Conversation::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [now()->subDays(6), now()])
            ->groupBy('date')
            ->pluck('count', 'date');

        return view('admin.dashboard', compact(
            'stats', 'recentConversations', 'recentTickets', 'liveVisitors', 'weeklyChats'
        ));
    }

    public function stats()
    {
        // Polled every 10s by the admin layout - a cheap moment to clear stale "online" flags.
        Visitor::expireStale();

        return response()->json([
            'active_visitors' => Visitor::online()->count(),
            // ids of visitors online now: the layout rings for every id it hasn't seen yet
            'online_ids'      => Visitor::online()->orderByDesc('last_activity_at')->limit(300)->pluck('id'),
            'active_chats'    => Conversation::whereIn('status', ['active', 'pending'])->count(),
            'unread_messages' => Conversation::sum('unread_admin'),
            'open_tickets'    => Ticket::where('status', 'open')->count(),
            // everything that should ring the admin bell
            'dm_unread'       => \App\Models\DirectMessage::where('sender_type', 'ticket_user')->where('recipient_type', 'admin')->where('is_read', false)->count(),
            'ticket_unread'   => (int) Ticket::sum('unread_admin'),
        ]);
    }
}
