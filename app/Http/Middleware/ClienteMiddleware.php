<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && in_array(Auth::user()->role, ['user', 'premium'])) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Acceso restringido a clientes.');
    }
}
