<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            if ($request->routeIs('portal.password.change', 'portal.password.update', 'logout')) {
                return $next($request);
            }

            return redirect()->route('portal.password.change')
                ->with('warning', 'You must change your password before continuing.');
        }

        return $next($request);
    }
}
