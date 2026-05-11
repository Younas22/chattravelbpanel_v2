<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\NationalHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::withCount('holidays')->orderBy('name')->get();
        return view('admin.countries.index', compact('countries'));
    }

    public function fetchHolidays(Request $request, Country $country)
    {
        $year = $request->input('year', date('Y'));
        $code = strtoupper($country->iso2);

        $response = Http::timeout(15)->get("https://tallyfy.com/national-holidays/api/{$code}/{$year}.json");

        if ($response->failed()) {
            $status = $response->status();
            return response()->json([
                'error' => "API returned {$status} for country code '{$code}'. This country may not be supported."
            ], 422);
        }

        $data = $response->json();
        $holidays = $data['holidays'] ?? [];

        $inserted = 0;
        foreach ($holidays as $h) {
            $exists = NationalHoliday::where('country_id', $country->id)
                ->where('year', $year)
                ->where('date', $h['date'])
                ->where('name', $h['name'])
                ->exists();

            if (!$exists) {
                NationalHoliday::create([
                    'country_id'          => $country->id,
                    'year'                => $year,
                    'date'                => $h['date'],
                    'name'                => $h['name'],
                    'local_name'          => $h['local_name'] ?? null,
                    'type'                => $h['type'] ?? 'national',
                    'observed_date'       => $h['observed_date'] ?? $h['date'],
                    'is_observed_shifted' => $h['is_observed_shifted'] ?? false,
                    'description'         => $h['description'] ?? null,
                ]);
                $inserted++;
            }
        }

        return response()->json([
            'success'  => true,
            'inserted' => $inserted,
            'total'    => count($holidays),
            'message'  => "{$inserted} new holidays saved for {$country->name} ({$year}).",
        ]);
    }

    public function holidays(Request $request, Country $country)
    {
        $query = $country->holidays()->orderBy('date');
        if ($request->filled('year')) {
            $query->where('year', $request->integer('year'));
        }
        return response()->json($query->get());
    }
}
