<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Pastikan user sudah login DAN role-nya adalah admin
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Akses ditolak! Anda bukan Admin.'
            ], 403);
        }

        return $next($request);
    }
}