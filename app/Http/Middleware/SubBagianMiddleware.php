<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SubBagianMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'user' || !$user->sub_bagian_id) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}