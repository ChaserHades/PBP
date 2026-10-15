<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            $exemptRoutes = [
                'password.force_change',
                'password.force_change.update',
                'logout',
            ];

            if (!in_array($request->route()?->getName(), $exemptRoutes)) {
                return redirect()->route('password.force_change')
                    ->with('warning', 'Admin telah mereset kata sandi Anda. Anda wajib memperbarui kata sandi sebelum melanjutkan.');
            }
        }

        return $next($request);
    }
}
