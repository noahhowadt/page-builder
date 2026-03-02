<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $prefix = trim((string) config('fortify.prefix', ''), '/');

        $loginPath = $prefix !== '' ? "/{$prefix}/login" : '/login';

        return redirect($loginPath);
    }
}

