<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $base = fn () => Visitor::where(function ($q) {
            $q->whereNotNull('name')
              ->orWhereNotNull('email')
              ->orWhereNotNull('whatsapp');
        });

        $query = $base()->with(['latestConversation']);

        if ($status !== 'all' && array_key_exists($status, Visitor::LEAD_STATUSES)) {
            $query->where('lead_status', $status);
        }

        $leads = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        $counts = $base()
            ->selectRaw('lead_status, COUNT(*) as count')
            ->groupBy('lead_status')
            ->pluck('count', 'lead_status');

        $total = $counts->sum();

        return view('admin.leads.index', compact('leads', 'status', 'counts', 'total'));
    }

    public function updateStatus(Request $request, Visitor $visitor)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Visitor::LEAD_STATUSES)),
        ]);

        $visitor->update([
            'lead_status'             => $request->status,
            'lead_status_updated_at'  => now(),
        ]);

        return response()->json([
            'ok'         => true,
            'status'     => $visitor->lead_status,
            'label'      => $visitor->lead_status_label,
            'updated_at' => $visitor->lead_status_updated_at->diffForHumans(),
        ]);
    }
}
