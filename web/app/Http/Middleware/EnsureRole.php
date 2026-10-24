<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    private const HIERARCHY = [
        'soldier' => 10,
        'base_admin' => 20,
        'hq_admin' => 30,
        'system_admin' => 40,
    ];

    public function handle(Request $request, Closure $next, string $minimumRole): Response
    {
        $user = $request->user();
        $requiredLevel = self::HIERARCHY[$minimumRole] ?? null;
        $userLevel = $user ? (self::HIERARCHY[$user->role] ?? 0) : 0;

        abort_unless($requiredLevel !== null && $userLevel >= $requiredLevel, 403);

        return $next($request);
    }
}
