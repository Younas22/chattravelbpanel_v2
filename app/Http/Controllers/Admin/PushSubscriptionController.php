<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use App\Services\PushNotifier;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /** Save (or refresh) this device's push subscription for the logged-in admin. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'endpoint'        => 'required|url|max:2000',
            'keys.p256dh'     => 'required|string|max:255',
            'keys.auth'       => 'required|string|max:255',
            'contentEncoding' => 'nullable|string|in:aesgcm,aes128gcm',
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => PushSubscription::hashEndpoint($data['endpoint'])],
            [
                'user_id'          => auth()->id(),
                'endpoint'         => $data['endpoint'],
                'public_key'       => $data['keys']['p256dh'],
                'auth_token'       => $data['keys']['auth'],
                'content_encoding' => $data['contentEncoding'] ?? 'aes128gcm',
                'user_agent'       => substr((string) $request->userAgent(), 0, 255),
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['endpoint' => 'required|string|max:2000']);

        PushSubscription::where('endpoint_hash', PushSubscription::hashEndpoint($request->endpoint))
            ->where('user_id', auth()->id())
            ->delete();

        return response()->json(['ok' => true]);
    }

    /** Send a test alert to this admin's devices right away. */
    public function test()
    {
        $result = PushNotifier::send(
            PushNotifier::payload('🔔 Notifications are on', 'You will be alerted here when a new message arrives.', route('admin.dashboard'), 'tbp-test'),
            auth()->id()
        );

        return response()->json($result + ['enabled' => PushNotifier::enabled()]);
    }
}
