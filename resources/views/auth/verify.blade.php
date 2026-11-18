@extends('layouts.app')

@section('content')
<section class="auth-page password-page">
    <div class="auth-card password-card">
        <div class="auth-intro password-intro">
            <span class="auth-symbol">✉</span>
            <p class="eyebrow">VERIFIKASI AKUN</p>
            <h1>Cek kotak<br><em>masukmu.</em></h1>
            <p>Verifikasi email membantu menjaga keamanan akun dan aktivitas peminjamanmu.</p>
        </div>
        <div class="auth-form-wrap">
            <h2>Verifikasi email</h2>
            <p class="form-subtitle">Buka tautan verifikasi yang kami kirim ke emailmu.</p>
            @if (session('resent'))
                <div class="flash flash-success" role="status">Tautan verifikasi baru sudah dikirim.</div>
            @endif
            <p class="muted">Belum menerima email? Periksa folder spam atau kirim ulang tautannya.</p>
            <form method="POST" action="{{ route('verification.resend', [], false) }}">
                @csrf
                <button type="submit" class="button button-primary auth-submit">Kirim ulang tautan <span>↗</span></button>
            </form>
        </div>
    </div>
</section>
@endsection
