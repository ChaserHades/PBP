<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // SUPERADMIN has access to everything
        if ($user->role === 'SUPERADMIN') {
            return $next($request);
        }

        // Check if user's role is in allowed roles
        if (!in_array($user->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '403 Forbidden: Akses tidak diizinkan.'], 403);
            }

            return match ($user->role) {
                'ADMIN' => redirect()->route('admin.dashboard')->with('error', 'Akses ditolak ke halaman tersebut.'),
                'TEKNISI' => redirect()->route('petugas.dashboard')->with('error', 'Akses ditolak ke halaman tersebut.'),
                'UNIVERSITAS' => redirect()->route('universitas.dashboard')->with('error', 'Akses ditolak ke halaman tersebut.'),
                default => abort(403, 'Akses Ditolak.'),
            };
        }

        return $next($request);
    }
}
