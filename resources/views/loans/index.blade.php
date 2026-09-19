@extends('layouts.app')

@section('content')
<section class="dashboard-head loan-page-head">
    <div><p class="eyebrow">{{ $isAdmin ? 'PUSAT KONTROL ADMIN' : 'RUANG PRIBADI' }}</p><h1>{{ $isAdmin ? 'Kelola peminjaman.' : 'Peminjaman saya.' }}</h1><p class="muted">{{ $isAdmin ? 'Perbarui status transaksi dan pantau tenggat pengembalian.' : 'Pantau buku yang sedang kamu pinjam dan tanggal pengembaliannya.' }}</p></div>
    <a class="button button-primary" href="{{ $isAdmin ? route('home') : url('/') . '#katalog' }}">{{ $isAdmin ? 'Dashboard' : 'Cari buku' }} <span>↗</span></a>
</section>

<section class="loan-table-panel">
    <div class="loan-table-head"><div><p class="eyebrow">DAFTAR TRANSAKSI</p><h2>{{ $isAdmin ? 'Semua peminjaman' : 'Riwayat bukumu' }}</h2></div><span class="section-count">{{ $loans->count() }} transaksi</span></div>
    @forelse($loans as $loan)
        @php($isLate = in_array($loan->status, ['borrowed', 'overdue']) && $loan->tenggat_kembali->isPast())
        <article class="loan-row">
            <span class="mini-cover">{{ strtoupper(substr($loan->book->judul, 0, 1)) }}</span>
            <div class="loan-book"><strong>{{ $loan->book->judul }}</strong><small>{{ $loan->book->penulis }}@if($isAdmin) · {{ $loan->user->name }}@endif</small></div>
            <div class="loan-date"><small>Dipinjam</small><strong>{{ $loan->tanggal_pinjam->format('d M Y') }}</strong></div>
            <div class="loan-date"><small>Jatuh tempo</small><strong class="{{ $isLate ? 'late-text' : '' }}">{{ $loan->tenggat_kembali->format('d M Y') }}</strong></div>
            @if($isAdmin)
                <div class="loan-admin-actions"><span class="loan-status status-{{ $loan->status }}">{{ $isLate ? 'Terlambat' : ($loan->status === 'returned' && $loan->total_denda > 0 ? 'Dikembalikan + denda' : ucfirst($loan->status)) }}</span><div class="loan-admin-controls">@if($loan->status !== 'returned')<details class="admin-return"><summary>Proses kembali</summary><form method="POST" action="{{ route('admin.loans.return.process', $loan) }}">@csrf<label>Kondisi buku<select name="keterangan_kondisi" required><option value="">Pilih kondisi</option><option value="baik">Baik</option><option value="rusak_ringan">Rusak ringan</option><option value="rusak_berat">Rusak berat</option><option value="hilang">Hilang</option></select></label><label>Denda tambahan<input type="number" name="denda_tambahan" min="0" step="1000" value="0"></label><label>Catatan admin<textarea name="catatan_admin" rows="2" placeholder="Catatan kondisi buku"></textarea></label><button type="submit" class="button button-primary">Simpan pengembalian</button></form></details>@endif<form method="POST" action="{{ route('admin.loans.status', $loan) }}" class="status-form">@csrf @method('PATCH')<select name="status" aria-label="Status {{ $loan->book->judul }}" onchange="this.form.submit()">@foreach(['pending', 'borrowed', 'overdue', 'returned', 'rejected'] as $status)<option value="{{ $status }}" @selected($loan->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select></form></div></div>
            @else
                <span class="loan-status status-{{ $loan->status }}">{{ $isLate ? 'Terlambat' : ($loan->status === 'returned' && $loan->total_denda > 0 ? 'Dikembalikan + denda' : ucfirst($loan->status)) }}</span>
            @endif
        </article>
    @empty
        <div class="loan-empty"><span>✦</span><h3>Belum ada peminjaman</h3><p class="muted">Buku yang kamu pinjam akan muncul di sini.</p></div>
    @endforelse
</section>
@endsection