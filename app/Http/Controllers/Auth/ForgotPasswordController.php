<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminPasswordResetCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm(): View
    {
        return view('auth.passwords.email');
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:8'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $rateLimitKey = 'password-reset:'.hash('sha256', $credentials['email']);
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            throw ValidationException::withMessages([
                'code' => 'Terlalu banyak percobaan. Minta kode baru kepada admin dan coba lagi nanti.',
            ]);
        }
        RateLimiter::hit($rateLimitKey, 60);

        $updated = DB::transaction(function () use ($credentials): bool {
            $user = User::query()
                ->where('email', $credentials['email'])
                ->where('role', 'siswa')
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (! $user) {
                return false;
            }

            $resetCode = AdminPasswordResetCode::query()
                ->where('user_id', $user->id)
                ->whereNull('used_at')
                ->lockForUpdate()
                ->first();

            if (
                ! $resetCode
                || $resetCode->expires_at->isPast()
                || ! Hash::check($credentials['code'], $resetCode->code_hash)
            ) {
                return false;
            }

            $resetCode->update(['used_at' => now()]);
            $user->forceFill([
                'password' => $credentials['password'],
                'remember_token' => Str::random(60),
            ])->save();

            return true;
        });

        if (! $updated) {
            throw ValidationException::withMessages([
                'code' => 'Kode tidak valid atau sudah kedaluwarsa. Minta kode baru kepada admin.',
            ]);
        }

        RateLimiter::clear($rateLimitKey);

        return redirect()->route('login')->with('status', 'Password berhasil diubah. Silakan masuk dengan password baru.');
    }
}
