@extends('layouts.app')

@section('content')
<section class="auth-page password-page">
    <div class="auth-card password-card">
        <div class="auth-intro password-intro">
            <span class="auth-symbol">↗</span>
            <p class="eyebrow">AKSES AKUN</p>
            <h1>Kembali ke<br><em>ruang bacamu.</em></h1>
            <p>Minta kode sekali pakai kepada admin, lalu gunakan kode tersebut untuk membuat password baru.</p>
        </div>
        <div class="auth-form-wrap">
            <h2>Lupa password?</h2>
            <p class="form-subtitle">Kode reset hanya berlaku untuk akun yang dipilih admin selama 15 menit.</p>
            @if (session('status'))
                <div class="flash flash-success" role="status">{{ session('status') }}</div>
            @endif
            <form method="POST" action="{{ route('password.email', [], false) }}">
                @csrf
                <div class="field">
                    <label for="email">Alamat email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="nama@email.com">
                    @error('email')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="code">Kode reset dari admin</label>
                    <input id="code" type="text" name="code" value="{{ old('code') }}" required inputmode="numeric" pattern="[0-9]{8}" minlength="8" maxlength="8" autocomplete="one-time-code" placeholder="8 digit kode">
                    @error('code')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password">Password baru</label>
                    <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter">
                    @error('password')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password-confirm">Ulangi password baru</label>
                    <input id="password-confirm" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Ketik ulang password">
                </div>
                <button type="submit" class="button button-primary auth-submit">Ubah password <span>↗</span></button>
            </form>
            <p class="auth-switch">Belum menerima kode? Datangi admin perpustakaan untuk meminta kode reset.</p>
            <a class="forgot-link" href="{{ route('login') }}">← Kembali ke halaman masuk</a>
        </div>
    </div>
</section>
@endsection
