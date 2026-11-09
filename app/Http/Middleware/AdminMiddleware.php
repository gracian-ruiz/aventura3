<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $role = Auth::user()->role;

            if ($role === 'admin') {
                return $next($request);
            }

            if (in_array($role, ['taller', 'mecanico'], true) && $request->routeIs('mecanico.*')) {
                return $next($request);
            }
        }

        return redirect('/')->with('error', 'No tienes permisos para acceder.');
    }
}
