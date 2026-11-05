<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && (bool) $user->must_change_password) {
            if ($request->routeIs('password.force_change', 'password.force_change.update', 'logout')) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda wajib mengganti sandi sebelum melanjutkan.',
                ], 403);
            }

            return redirect()->route('password.force_change');
        }

        return $next($request);
    }
}
