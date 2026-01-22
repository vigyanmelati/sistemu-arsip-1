<?php
// app/Http/Middleware/CheckAdmin.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || (Auth::user()->isUser() && !Auth::user()->isSuperAdmin())) {
            return redirect('/dashboard')->with('error', 'Anda tidak memiliki akses.');
        }

        return $next($request);
    }
}