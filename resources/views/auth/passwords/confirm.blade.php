@extends('layouts.app')

@section('content')
<section class="auth-page password-page">
    <div class="auth-card password-card">
        <div class="auth-intro password-intro">
            <span class="auth-symbol">L</span>
            <p class="eyebrow">KEAMANAN AKUN</p>
            <h1>Satu langkah<br><em>untuk lanjut.</em></h1>
            <p>Konfirmasi password sebelum membuka pengaturan sensitif akunmu.</p>
        </div>
        <div class="auth-form-wrap">
            <h2>Konfirmasi password</h2>
            <p class="form-subtitle">Masukkan password akunmu untuk melanjutkan.</p>
            <form method="POST" action="{{ route('password.confirm', [], false) }}" autocomplete="off">
                @csrf
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" autofocus>
                    @error('password')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="button button-primary auth-submit">Konfirmasi <span>↗</span></button>
            </form>
            <a class="forgot-link" href="{{ route('password.request') }}">Lupa password?</a>
        </div>
    </div>
</section>
@endsection
