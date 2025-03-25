<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TallerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'taller') {
            return $next($request);
        }

        return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder a esta sección.');
    }
}
