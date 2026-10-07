<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pastikan user sudah login dan rolenya adalah 'admin'
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        // Jika bukan admin (misal user biasa), redirect ke halaman utama/events mereka 
        // atau abort 403 (Unauthorized)
        return redirect('/events')->with('error', 'Anda tidak memiliki akses ke halaman admin.');
    }
}