<?php

namespace App\Http\Controllers;

use App\Models\WidgetSetting;
use App\Services\PwaIcons;
use Illuminate\Http\Response;

/**
 * Installable admin app (PWA): web manifest + service worker.
 *
 * Both are served through Laravel (not as static files) so they work whether
 * the app lives at the domain root or in a sub-folder, and so the manifest can
 * use the system name from settings. URLs inside are relative to the site root.
 */
class PwaController extends Controller
{
    public function manifest()
    {
        $name = WidgetSetting::get('system_name', 'TBP Chat');

        return response()->json([
            'id'               => 'admin',
            'name'             => $name,
            'short_name'       => mb_strimwidth($name, 0, 12, ''),
            'description'      => 'Live chat & support dashboard',
            'start_url'        => 'admin/dashboard?source=pwa',
            'scope'            => 'admin/',
            'display'          => 'standalone',
            'display_override' => ['standalone', 'minimal-ui'],
            'orientation'      => 'portrait',
            'background_color' => '#ffffff',
            'theme_color'      => '#2563eb',
            // Built from the favicon in Widget Settings (falls back to the default icons)
            'icons' => [
                ['src' => PwaIcons::url('icon-192.png', true),     'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => PwaIcons::url('icon-512.png', true),     'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => PwaIcons::url('maskable-512.png', true), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            'shortcuts' => [
                ['name' => 'Live Chats', 'url' => 'admin/conversations', 'icons' => [['src' => PwaIcons::url('icon-192.png', true), 'sizes' => '192x192']]],
                ['name' => 'Messages',   'url' => 'admin/messages',      'icons' => [['src' => PwaIcons::url('icon-192.png', true), 'sizes' => '192x192']]],
                ['name' => 'Tickets',    'url' => 'admin/tickets',       'icons' => [['src' => PwaIcons::url('icon-192.png', true), 'sizes' => '192x192']]],
            ],
        ], 200, [
            'Content-Type'  => 'application/manifest+json',
            'Cache-Control' => 'no-cache',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function serviceWorker(): Response
    {
        return response(view('pwa.sw')->render(), 200, [
            'Content-Type'  => 'application/javascript; charset=utf-8',
            // Browsers must always re-check the worker so updates roll out immediately.
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
