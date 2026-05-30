<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectEmployeeFromHR
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('Employee') && ! $user->hasRole(['Super Admin', 'HR Manager'])) {
            return redirect()->route('portal.dashboard');
        }

        return $next($request);
    }
}
