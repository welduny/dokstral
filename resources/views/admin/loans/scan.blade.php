@extends('layouts.app')

@section('content')
<section class="dashboard-head admin-page-head">
    <div><p class="eyebrow">AREA ADMIN</p><h1>Konfirmasi serah-terima.</h1><p class="muted">Kamera akan langsung membaca QR saat halaman terbuka.</p></div>
    <a class="button button-primary" href="{{ route('admin.loans.index') }}">Daftar peminjaman <span>↗</span></a>
</section>
<section class="scan-panel">
    <div id="handoff-reader" data-confirm-url="{{ route('admin.loans.confirm-handoff') }}" data-success-url="{{ route('admin.loans.index', [], false) }}"></div>
    <form id="scan-manual" class="scan-manual" novalidate>
        <label for="scan-token">Atau masukkan kode peminjaman</label>
        <div class="scan-manual-controls">
            <input id="scan-token" name="token" type="text" inputmode="numeric" pattern="[0-9]{5}" maxlength="5" autocomplete="off" placeholder="5 digit kode" aria-describedby="scan-manual-hint">
            <button type="submit">Konfirmasi kode</button>
        </div>
        <small id="scan-manual-hint">Masukkan kode 5 digit yang tertera pada QR peminjaman.</small>
    </form>
    <div id="scan-result" class="scan-result" role="status" aria-live="polite">Memulai kamera...</div>
</section>
@endsection
