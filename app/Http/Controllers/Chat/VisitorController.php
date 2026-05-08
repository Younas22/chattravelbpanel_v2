<?php

namespace App\Http\Controllers\Chat;

use App\Events\VisitorStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Visitor;
use App\Models\VisitorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VisitorController extends Controller
{
    public function identify(Request $request)
    {
        $request->validate([
            'page_url'   => 'required|string|max:2000',
            'page_title' => 'nullable|string|max:500',
            'referrer'   => 'nullable|string|max:2000',
            'session_id' => 'nullable|string|max:64',
        ]);

        $sessionId = $request->session_id ?: Str::random(40);
        $ip = $request->ip();

        $visitor = Visitor::firstOrNew(['session_id' => $sessionId]);
        $isNew = !$visitor->exists;

        $visitor->fill([
            'ip_address'       => $ip,
            'current_page'     => $request->page_url,
            'landing_page'     => $visitor->landing_page ?: $request->page_url,
            'referrer'         => $visitor->referrer ?: $request->referrer,
            'browser'          => $this->getBrowser($request->userAgent()),
            'os'               => $this->getOS($request->userAgent()),
            'device'           => $this->getDevice($request->userAgent()),
            'is_online'        => true,
            'last_activity_at' => now(),
        ]);

        // Try geo-location from IP (simple approach)
        if ($isNew) {
            $geo = $this->getGeoInfo($ip);
            $visitor->country      = $geo['country'] ?? null;
            $visitor->country_code = $geo['country_code'] ?? null;
            $visitor->city         = $geo['city'] ?? null;
        }

        $visitor->save();

        // Log page visit
        VisitorLog::create([
            'visitor_id' => $visitor->id,
            'page_url'   => $request->page_url,
            'page_title' => $request->page_title,
            'visited_at' => now(),
        ]);

        if ($isNew) {
            broadcast(new VisitorStatusChanged($visitor, 'new'));
        } else {
            broadcast(new VisitorStatusChanged($visitor, 'online'));
        }

        return response()->json([
            'session_id' => $sessionId,
            'visitor_id' => $visitor->id,
        ]);
    }

    public function heartbeat(Request $request)
    {
        $visitor = Visitor::where('session_id', $request->session_id)->first();
        if ($visitor) {
            $visitor->update(['is_online' => true, 'last_activity_at' => now()]);
        }
        return response()->json(['ok' => true]);
    }

    public function offline(Request $request)
    {
        $visitor = Visitor::where('session_id', $request->session_id)->first();
        if ($visitor) {
            $visitor->update(['is_online' => false]);
            broadcast(new VisitorStatusChanged($visitor, 'offline'));
        }
        return response()->json(['ok' => true]);
    }

    private function getBrowser(string $ua): string
    {
        if (str_contains($ua, 'Chrome') && !str_contains($ua, 'Edg')) return 'Chrome';
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Safari') && !str_contains($ua, 'Chrome')) return 'Safari';
        if (str_contains($ua, 'Edg')) return 'Edge';
        if (str_contains($ua, 'OPR') || str_contains($ua, 'Opera')) return 'Opera';
        return 'Unknown';
    }

    private function getOS(string $ua): string
    {
        if (str_contains($ua, 'Windows')) return 'Windows';
        if (str_contains($ua, 'Mac OS')) return 'macOS';
        if (str_contains($ua, 'Linux')) return 'Linux';
        if (str_contains($ua, 'Android')) return 'Android';
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) return 'iOS';
        return 'Unknown';
    }

    private function getDevice(string $ua): string
    {
        if (str_contains($ua, 'Mobile') || str_contains($ua, 'Android')) return 'mobile';
        if (str_contains($ua, 'Tablet') || str_contains($ua, 'iPad')) return 'tablet';
        return 'desktop';
    }

    private function getGeoInfo(string $ip): array
    {
        // Use free ip-api.com (no key needed, 45 req/min limit)
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return ['country' => 'Local', 'country_code' => 'LO', 'city' => 'Localhost'];
        }

        try {
            $context = stream_context_create(['http' => ['timeout' => 2]]);
            $json    = @file_get_contents("http://ip-api.com/json/{$ip}?fields=country,countryCode,city", false, $context);
            if ($json) {
                $data = json_decode($json, true);
                return [
                    'country'      => $data['country'] ?? null,
                    'country_code' => $data['countryCode'] ?? null,
                    'city'         => $data['city'] ?? null,
                ];
            }
        } catch (\Throwable) {}

        return [];
    }
}
