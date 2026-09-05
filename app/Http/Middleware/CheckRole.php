<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasRole($roles)) {
            abort(403, 'Unauthorized. Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
