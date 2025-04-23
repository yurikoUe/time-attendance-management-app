<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {

        if ($role === 'admin' && Auth::guard('admin')->check()) {
            return $next($request);
        }

        if ($role === 'user' && Auth::guard('web')->check()) {
            return $next($request);
        }

        abort(403, 'Unauthorized access.');
    }



}
