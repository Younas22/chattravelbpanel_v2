<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\NationalHoliday;
use App\Models\Visitor;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $visitors  = Visitor::orderByDesc('last_activity_at')->paginate(30);
        $liveCount = Visitor::where('is_online', true)->count();

        // Collect unique country_codes from current page
        $codes = $visitors->pluck('country_code')->filter()->unique()->values();

        // Map iso2 -> country_id
        $countries = Country::whereIn('iso2', $codes)->get()->keyBy('iso2');

        $today         = now();
        $countryHolidays = [];

        foreach ($countries as $iso2 => $country) {
            // today first, else next upcoming this month
            $h = NationalHoliday::where('country_id', $country->id)
                ->where('date', $today->toDateString())
                ->first();

            if (!$h) {
                $h = NationalHoliday::where('country_id', $country->id)
                    ->whereBetween('date', [$today->toDateString(), $today->copy()->endOfMonth()->toDateString()])
                    ->orderBy('date')
                    ->first();
            }

            if ($h) {
                $countryHolidays[$iso2] = [
                    'name'       => $h->name,
                    'local_name' => $h->local_name,
                    'date'       => $h->date->toDateString(),
                    'is_today'   => $h->date->isToday(),
                ];
            }
        }

        return view('admin.visitors.index', compact('visitors', 'liveCount', 'countryHolidays'));
    }

    public function live()
    {
        $visitors = Visitor::where('is_online', true)
            ->orderByDesc('last_activity_at')
            ->get(['id', 'session_id', 'name', 'country', 'country_code', 'browser', 'os', 'device', 'current_page', 'last_activity_at', 'is_online']);
        return response()->json($visitors);
    }

    public function show(Visitor $visitor)
    {
        $visitor->load(['conversations.latestMessage', 'logs' => fn($q) => $q->orderByDesc('visited_at')->limit(20)]);
        return response()->json($visitor);
    }

    public function cleanup()
    {
        // Mark visitors inactive if no activity for 5 minutes
        Visitor::where('is_online', true)
            ->where('last_activity_at', '<', now()->subMinutes(5))
            ->update(['is_online' => false]);

        return response()->json(['cleaned' => true]);
    }
}
