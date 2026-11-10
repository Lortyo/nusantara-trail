<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        if ($request->is('api/*') || $request->expectsJson()){
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        return redirect('/events')->with('error', 'Anda tidak memiliki akses ke halaman admin.');
    }
}