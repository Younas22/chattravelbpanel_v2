<?php

namespace App\Http\Middleware;

use App\Models\Visitor;
use App\Models\VisitorLog;
use App\Events\VisitorStatusChanged;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    public function handle(Request $request, Closure $next): Response
    {
        $sessionId = $request->header('X-Visitor-Session') ?? $request->query('vsid');

        if ($sessionId) {
            $visitor = Visitor::where('session_id', $sessionId)->first();

            if ($visitor) {
                $wasOnline = $visitor->is_online;
                $visitor->update([
                    'is_online'        => true,
                    'last_activity_at' => now(),
                    'current_page'     => $request->header('X-Current-Page', $visitor->current_page),
                ]);

                if (!$wasOnline) {
                    broadcast(new VisitorStatusChanged($visitor, 'online'));
                }

                $request->attributes->set('visitor', $visitor);
            }
        }

        return $next($request);
    }
}
