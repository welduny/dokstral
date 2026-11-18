@extends('layouts.app')

@section('content')
<section class="dashboard-head admin-page-head">
    <div><p class="eyebrow">AREA ADMIN · AKUN PENGGUNA</p><h1>Edit akun.</h1><p class="muted">Perbarui identitas atau bantu anggota mengganti password.</p></div>
    <a class="button button-primary" href="{{ route('admin.users.index') }}">Kembali ke pengguna <span>↗</span></a>
</section>

<section class="admin-form-panel">
    <div class="admin-form-heading"><p class="eyebrow">DATA AKUN</p><h2>{{ $user->name }}</h2></div>
    <form method="POST" action="{{ route('admin.users.update', $user, false) }}" class="book-create-form">
        @csrf @method('PATCH')
        <div class="admin-field"><label for="name">Nama pengguna</label><input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255">@error('name')<small>{{ $message }}</small>@enderror</div>
        <div class="admin-field"><label for="nis">Nomor Induk Siswa (NIS)</label><input id="nis" name="nis" value="{{ old('nis', $user->nis) }}" inputmode="numeric" maxlength="30">@error('nis')<small>{{ $message }}</small>@enderror</div>
        <div class="admin-field"><label for="kelas">Kelas</label><input id="kelas" name="kelas" value="{{ old('kelas', $user->kelas) }}" maxlength="50">@error('kelas')<small>{{ $message }}</small>@enderror</div>
        <div class="admin-field"><label for="email">Alamat email</label><input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255">@error('email')<small>{{ $message }}</small>@enderror</div>
        <div class="admin-field">
            <label for="status">Status akun</label>
            <select id="status" name="status" required>
                <option value="active" @selected(old('status', $user->status) === 'active')>Aktif</option>
                <option value="inactive" @selected(old('status', $user->status) === 'inactive')>Nonaktif</option>
                @if($user->status === 'pending')<option value="pending" @selected(old('status', $user->status) === 'pending')>Menunggu verifikasi</option>@endif
            </select>
            @error('status')<small>{{ $message }}</small>@enderror
            <small class="field-hint">Akun nonaktif tidak dapat masuk atau menggunakan kode reset password.</small>
        </div>
        <button type="submit" class="button button-primary">Simpan perubahan <span>↗</span></button>
    </form>
</section>

<section class="admin-form-panel admin-user-reset-panel">
    <div class="admin-form-heading"><p class="eyebrow">BANTU ANGGOTA</p><h2>Reset password</h2></div>
    <p class="muted">Buat kode 8 digit sekali pakai untuk <strong>{{ $user->email }}</strong>. Kode berlaku 15 menit dan kode sebelumnya langsung tidak berlaku.</p>
    @if(session('issued_reset_code'))
        <div class="admin-issued-code" role="status" aria-live="polite">
            <strong>Kode reset — berikan hanya kepada pengguna ini</strong>
            <code>{{ session('issued_reset_code.code') }}</code>
            <small>Kode akan kedaluwarsa pukul {{ session('issued_reset_code.expires_at') }}. Setelah meninggalkan halaman, kode tidak akan ditampilkan lagi.</small>
        </div>
    @endif
    <form method="POST" action="{{ route('admin.users.password-reset-code', $user, false) }}" onsubmit="return confirm('Buat kode baru? Kode sebelumnya akan langsung tidak berlaku.')">
        @csrf
        <button class="button button-primary" type="submit" @disabled($user->status !== 'active')>Buat kode reset <span>↗</span></button>
    </form>
</section>

<section class="admin-form-panel admin-user-delete-panel">
    <div class="admin-form-heading"><p class="eyebrow">TINDAKAN PERMANEN</p><h2>Hapus akun</h2></div>
    <p class="muted">Akun dengan riwayat peminjaman tidak dapat dihapus agar catatan transaksi tetap tersimpan. Nonaktifkan akun tersebut sebagai gantinya.</p>
    <form method="POST" action="{{ route('admin.users.destroy', $user, false) }}" onsubmit="return confirm({{ Js::from('Hapus akun '.$user->name.'? Tindakan ini tidak dapat dibatalkan.') }})">
        @csrf @method('DELETE')
        <button class="loan-delete-button" type="submit">Hapus akun pengguna</button>
    </form>
</section>
@endsection
