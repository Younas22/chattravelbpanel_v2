<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Defer\DeferredCallbackCollection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * defer() work (e.g. sending push notifications) runs after the response is
 * sent. PHP-FPM / LiteSpeed close the connection first, but Apache mod_php
 * (XAMPP, some shared hosts) keeps it open until the script ends, so the
 * browser would wait for the push to finish. Giving the response an explicit
 * Content-Length lets the client finish as soon as the body has arrived.
 */
class FinishResponseBeforeDeferredWork
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof StreamedResponse || $response instanceof BinaryFileResponse) {
            return $response;
        }

        $deferred = app()->bound(DeferredCallbackCollection::class)
            ? app(DeferredCallbackCollection::class)->count()
            : 0;

        if ($deferred > 0 && ! $response->headers->has('Content-Length')) {
            $content = $response->getContent();
            if (is_string($content)) {
                $response->headers->set('Content-Length', (string) strlen($content));
                $response->headers->set('Connection', 'close');
            }
        }

        return $response;
    }
}
