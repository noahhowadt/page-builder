<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;

class SetupAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $userCount = User::count();
        $setupToken = env('SETUP_TOKEN');

        // If users already exist, block setup
        if ($userCount > 0) {
            return redirect()->route('login');
        }

        // If no users and no token, show locked page
        if ($userCount === 0 && empty($setupToken)) {
            return Inertia::render('auth/SetupError', [
                'reason' => 'locked'
            ]);
        }

        // If token is too weak, show error page
        if ($userCount === 0 && strlen($setupToken) < 32) {
            return Inertia::render('auth/SetupError', [
                'reason' => 'weak_token'
            ]);
        }

        return $next($request);
    }
}
