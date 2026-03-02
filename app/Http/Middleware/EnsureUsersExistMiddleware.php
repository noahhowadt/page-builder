<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class EnsureUsersExistMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (User::count() === 0) {
            return redirect()->route('setup');
        }

        return $next($request);
    }
}
