<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->status === 'active') {
            return $next($request);
        }

        $loginRoute = $user->role === 'admin' ? 'admin.login' : 'login';
        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = $user->status === 'pending'
            ? 'Akun kamu masih menunggu verifikasi admin sebelum bisa digunakan.'
            : 'Akun ini nonaktif. Hubungi administrator untuk meminta bantuan.';

        return redirect()->route($loginRoute)->with('error', $message);
    }
}
