<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{


    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    protected function attemptLogin(Request $request): bool
    {
        $user = User::query()->where('email', $request->input($this->username()))->first();

        if ($user?->role === 'admin' || $user?->status !== 'active') {
            return false;
        }

        return $this->guard()->attempt(
            $this->credentials($request),
            $request->boolean('remember')
        );
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $user = User::query()->where('email', $request->input($this->username()))->first();

        if (
            $user?->role === 'siswa'
            && $user->status === 'pending'
            && Hash::check($request->input('password'), $user->password)
        ) {
            throw ValidationException::withMessages([
                $this->username() => 'Akun kamu masih menunggu persetujuan admin. Coba masuk kembali setelah akun disetujui.',
            ]);
        }

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
