<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

use function Illuminate\Support\defer;

/**
 * Sends Web Push notifications to every admin device that installed the app
 * and allowed notifications. The phone shows the alert and plays its
 * notification sound even when the app is closed.
 */
class PushNotifier
{
    public static function enabled(): bool
    {
        return (bool) config('services.webpush.public_key') && (bool) config('services.webpush.private_key');
    }

    /**
     * Queue a notification to all admins. It is sent after the HTTP response has
     * gone out, so the visitor's / customer's request is never slowed down.
     */
    public static function notifyAdmins(string $title, string $body, string $url, string $tag = 'tbp-message'): void
    {
        if (! self::enabled()) {
            return;
        }

        $payload = self::payload($title, $body, $url, $tag);
        defer(fn () => self::send($payload));
    }

    public static function payload(string $title, string $body, string $url, string $tag): string
    {
        return json_encode([
            'title' => mb_strimwidth($title, 0, 80, '…'),
            'body'  => mb_strimwidth(trim(preg_replace('/\s+/', ' ', $body)), 0, 160, '…'),
            'url'   => $url,
            'tag'   => $tag,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Send right now. Returns ['sent' => n, 'failed' => n, 'removed' => n].
     */
    public static function send(string $payload, ?int $userId = null): array
    {
        $result = ['sent' => 0, 'failed' => 0, 'removed' => 0];
        if (! self::enabled()) {
            return $result;
        }

        $subs = PushSubscription::query()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->get();
        if ($subs->isEmpty()) {
            return $result;
        }

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject'    => config('services.webpush.subject'),
                    'publicKey'  => config('services.webpush.public_key'),
                    'privateKey' => config('services.webpush.private_key'),
                ],
            ], ['TTL' => 3600, 'urgency' => 'high'], 10);
            $webPush->setReuseVAPIDHeaders(true);

            foreach ($subs as $sub) {
                $webPush->queueNotification(Subscription::create([
                    'endpoint'        => $sub->endpoint,
                    'publicKey'       => $sub->public_key,
                    'authToken'       => $sub->auth_token,
                    'contentEncoding' => $sub->content_encoding,
                ]), $payload);
            }

            foreach ($webPush->flush() as $report) {
                $hash = PushSubscription::hashEndpoint($report->getEndpoint());
                if ($report->isSuccess()) {
                    $result['sent']++;
                    PushSubscription::where('endpoint_hash', $hash)->update(['last_used_at' => now()]);
                } elseif ($report->isSubscriptionExpired()) {
                    // App uninstalled / permission revoked: forget this device.
                    $result['removed'] += PushSubscription::where('endpoint_hash', $hash)->delete();
                } else {
                    $result['failed']++;
                    Log::warning('Web push failed: ' . $report->getReason());
                }
            }
        } catch (Throwable $e) {
            Log::warning('Web push error: ' . $e->getMessage());
            $result['failed'] = max($result['failed'], 1);
        }

        return $result;
    }
}
