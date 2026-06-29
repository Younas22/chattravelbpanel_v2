<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackTicketUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('ticket_user')->check()) {
            auth('ticket_user')->user()->update(['last_seen_at' => now()]);
        }

        return $next($request);
    }
}
