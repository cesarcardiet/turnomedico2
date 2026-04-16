<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->hasRole('doctor')) {
            $subscription = $user->subscriptions()->where('payment_status', 'approved')->where('ends_at', '>', now())->first();

            if (!$subscription) {
                // If they are not on membership related routes, redirect
                if (!$request->routeIs('doctor.membership.*') && !$request->routeIs('logout')) {
                    return redirect()->route('doctor.membership.index');
                }
            }
        }

        return $next($request);
    }
}
