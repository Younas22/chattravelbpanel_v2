<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\GlobalOffer;
use App\Models\NationalHoliday;
use App\Models\Visitor;
use Illuminate\Http\Request;

class OfferPopupController extends Controller
{
    public function popup(Request $request)
    {
        $offer = GlobalOffer::first();

        if (!$offer || !$offer->is_active) {
            return response()->json(['active' => false]);
        }

        // Resolve country code: from session_id or IP geo
        $countryCode = null;

        if ($request->filled('session_id')) {
            $visitor     = Visitor::where('session_id', $request->session_id)->first();
            $countryCode = $visitor?->country_code;
        }

        if (!$countryCode) {
            $countryCode = $this->detectCountryFromIp($request->ip());
        }

        $holiday = null;
        $today   = now();

        if ($countryCode) {
            $country = Country::where('iso2', strtoupper($countryCode))->first();

            if ($country) {
                // Prefer today's holiday, else next upcoming this month
                $holiday = NationalHoliday::where('country_id', $country->id)
                    ->where('date', $today->toDateString())
                    ->first();

                if (!$holiday) {
                    $holiday = NationalHoliday::where('country_id', $country->id)
                        ->whereBetween('date', [$today->toDateString(), $today->copy()->endOfMonth()->toDateString()])
                        ->orderBy('date')
                        ->first();
                }
            }
        }

        // Countdown target: end of holiday day, or end of today
        $countdownTo = $holiday
            ? $holiday->date->copy()->endOfDay()->toIso8601String()
            : $today->copy()->endOfDay()->toIso8601String();

        return response()->json([
            'active'         => true,
            'label'          => $offer->label,
            'original_price' => (float) $offer->original_price,
            'discount_price' => (float) $offer->discount_price,
            'holiday'        => $holiday ? [
                'name'       => $holiday->name,
                'local_name' => $holiday->local_name,
                'date'       => $holiday->date->toDateString(),
            ] : null,
            'countdown_to' => $countdownTo,
        ]);
    }

    private function detectCountryFromIp(string $ip): ?string
    {
        if (in_array($ip, ['127.0.0.1', '::1'])) return null;

        try {
            $context = stream_context_create(['http' => ['timeout' => 2]]);
            $json    = @file_get_contents("http://ip-api.com/json/{$ip}?fields=countryCode", false, $context);
            if ($json) {
                $data = json_decode($json, true);
                return $data['countryCode'] ?? null;
            }
        } catch (\Throwable) {}

        return null;
    }
}
