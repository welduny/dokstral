<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if ($user = auth()->user()) {
            if ($user->role === 'admin') {
                return redirect()->route('admin.loans.index');
            }

            return redirect()->route('home')->with('error', 'Keluar dari akun siswa sebelum masuk sebagai admin.');
        }

        return view('auth.admin-login');
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->user()) {
            return redirect()->route('admin.login')->with('error', 'Keluar dari akun saat ini sebelum masuk sebagai admin.');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = User::query()
            ->where('email', $credentials['email'])
            ->where('role', 'admin')
            ->where('status', 'active')
            ->first();

        if (!$admin || Hash::check('password', $admin->password) || !Hash::check($credentials['password'], $admin->password)) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'Email atau password admin tidak valid.']);
        }

        Auth::login($admin, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('admin.loans.index'));
    }
}