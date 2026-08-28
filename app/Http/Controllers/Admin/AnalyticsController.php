<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Ticket;
use App\Models\Visitor;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index()
    {
        $period = (int) request('period', 7);
        if (!in_array($period, [7, 30, 90], true)) {
            $period = 7;
        }

        $conversations = Conversation::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [now()->subDays($period - 1)->startOfDay(), now()->endOfDay()])
            ->groupBy('date')
            ->pluck('count', 'date');

        $messages = Message::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [now()->subDays($period - 1)->startOfDay(), now()->endOfDay()])
            ->groupBy('date')
            ->pluck('count', 'date');

        $visitors = Visitor::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [now()->subDays($period - 1)->startOfDay(), now()->endOfDay()])
            ->groupBy('date')
            ->pluck('count', 'date');

        $topCountries = Visitor::selectRaw('country, COUNT(*) as count')
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $topPages = Visitor::selectRaw('landing_page, COUNT(*) as count')
            ->whereNotNull('landing_page')
            ->groupBy('landing_page')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $devices = Visitor::selectRaw('device, COUNT(*) as count')
            ->groupBy('device')
            ->get()
            ->pluck('count', 'device');

        // Traffic sources — visitors grouped by UTM source (falls back to "Direct / Other")
        $trafficSources = Visitor::selectRaw('COALESCE(NULLIF(utm_source, \'\'), \'Direct / Other\') as source, COUNT(*) as count')
            ->whereBetween('created_at', [now()->subDays($period - 1)->startOfDay(), now()->endOfDay()])
            ->groupBy('source')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return view('admin.analytics.index', compact(
            'conversations', 'messages', 'visitors',
            'topCountries', 'topPages', 'devices', 'period', 'trafficSources'
        ));
    }
}
