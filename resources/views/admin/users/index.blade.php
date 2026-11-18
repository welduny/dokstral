@extends('layouts.app')

@section('content')
<section class="dashboard-head admin-page-head">
    <div><p class="eyebrow">AREA ADMIN</p><h1>Kelola akun pengguna.</h1><p class="muted">Periksa NIS pendaftar, setujui akun warga sekolah, dan kelola akses anggota.</p></div>
    <a class="button button-primary" href="{{ route('admin.books.create') }}">Kembali ke koleksi <span>↗</span></a>
</section>

<section class="admin-form-panel admin-user-panel">
    <div class="admin-form-heading"><p class="eyebrow">ANGGOTA</p><h2>Daftar akun</h2></div>
    <nav class="admin-user-filters" aria-label="Filter status akun">
        @foreach(['all' => 'Semua', 'pending' => 'Menunggu', 'active' => 'Aktif', 'inactive' => 'Nonaktif'] as $filter => $label)
            <a href="{{ route('admin.users.index', array_filter(['status' => $filter === 'all' ? null : $filter, 'q' => $search !== '' ? $search : null])) }}" @class(['is-selected' => $status === $filter]) @if($status === $filter) aria-current="page" @endif>
                {{ $label }} <span>{{ $counts[$filter] }}</span>
            </a>
        @endforeach
    </nav>
    <form class="admin-user-search" method="GET" action="{{ route('admin.users.index', [], false) }}">
        @if($status !== 'all')<input type="hidden" name="status" value="{{ $status }}">@endif
        <label class="sr-only" for="user-search">Cari nama atau email</label>
        <input id="user-search" type="search" name="q" value="{{ $search }}" placeholder="Cari nama, NIS, atau email">
        <button class="button button-primary" type="submit">Cari</button>
        @if($search !== '')<a class="text-link" href="{{ route('admin.users.index', array_filter(['status' => $status !== 'all' ? $status : null])) }}">Hapus pencarian</a>@endif
    </form>
    <div class="admin-user-list">
        @forelse($users as $user)
            <article class="admin-user-item">
                <div class="admin-user-avatar" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <div class="admin-user-copy">
                    <strong>{{ $user->name }}</strong>
                    <small>NIS {{ $user->nis ?? '—' }} · {{ $user->kelas ?? 'Kelas belum diisi' }} · {{ $user->email }}</small>
                </div>
                <span class="admin-user-status {{ $user->status === 'active' ? 'is-active' : ($user->status === 'pending' ? 'is-pending' : 'is-inactive') }}">{{ $user->status === 'active' ? 'Aktif' : ($user->status === 'pending' ? 'Menunggu verifikasi' : 'Nonaktif') }}</span>
                @if($user->status === 'pending')
                    <div class="admin-user-actions">
                        <form method="POST" action="{{ route('admin.users.approve', $user, false) }}">@csrf<button class="button button-primary" type="submit">Setujui</button></form>
                        <form method="POST" action="{{ route('admin.users.reject', $user, false) }}" onsubmit="return confirm({{ Js::from('Tolak pendaftaran '.$user->name.'? Akun ini tidak akan dapat digunakan.') }})">@csrf<button class="button admin-user-reject" type="submit">Tolak</button></form>
                        <a class="text-link" href="{{ route('admin.users.edit', $user) }}">Periksa data</a>
                    </div>
                @else
                    <a class="button button-primary" href="{{ route('admin.users.edit', $user) }}">Kelola akun <span>↗</span></a>
                @endif
            </article>
        @empty
            <p class="muted">{{ $search !== '' ? 'Tidak ada akun yang cocok dengan pencarian.' : ($status === 'pending' ? 'Tidak ada pendaftaran yang menunggu verifikasi.' : 'Belum ada akun pengguna pada status ini.') }}</p>
        @endforelse
    </div>
    @if($users->hasPages())<div class="catalog-pagination">{{ $users->links() }}</div>@endif
</section>
@endsection
