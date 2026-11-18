@extends('layouts.app')

@section('content')
<section class="dashboard-head loan-page-head">
    <div><p class="eyebrow">{{ $isAdmin ? 'PUSAT KONTROL ADMIN' : 'RUANG PRIBADI' }}</p><h1>{{ $isAdmin ? 'Kelola peminjaman.' : 'Peminjaman saya.' }}</h1><p class="muted">{{ $isAdmin ? 'Perbarui status transaksi dan pantau tenggat pengembalian.' : 'Pantau buku yang sedang kamu pinjam dan tanggal pengembaliannya.' }}</p></div>
    @if($isAdmin)<a class="button button-primary" href="{{ route('admin.loans.scan') }}">Scan bukti pinjam <span>↗</span></a>@endif
    <a class="button button-primary" href="{{ $isAdmin ? route('home') : url('/') . '#katalog' }}">{{ $isAdmin ? 'Dashboard' : 'Cari buku' }} <span>↗</span></a>
</section>

<section class="loan-table-panel">
    <div class="loan-table-head"><div><p class="eyebrow">DAFTAR TRANSAKSI</p><h2>{{ $isAdmin ? 'Semua peminjaman' : 'Riwayat bukumu' }}</h2></div><span class="section-count">{{ $loans->count() }} transaksi</span></div>
    @forelse($loans as $loan)
        @php($isLate = in_array($loan->status, ['borrowed', 'overdue']) && $loan->tenggat_kembali->isPast())
        <article class="loan-row" data-loan-id="{{ $loan->id }}" data-loan-status="{{ $loan->status }}" @if(!$isAdmin && $loan->status === 'pending') data-status-url="{{ route('loans.status', [], false) }}" @endif>
            <span class="mini-cover">{{ strtoupper(substr($loan->book->judul, 0, 1)) }}</span>
            <div class="loan-book">
                <strong>{{ $loan->book->judul }}</strong>
                <small>{{ $loan->book->penulis }}@if($isAdmin) · {{ $loan->user->name }}@endif</small>
                @if(!$isAdmin && $loan->status === 'pending' && $loan->confirmation_token)
                    <div class="handoff-qr"><canvas class="loan-confirmation-qr" data-token="{{ $loan->confirmation_token }}" aria-label="QR konfirmasi peminjaman"></canvas><div><strong>Tunjukkan QR ini ke admin</strong><small>Scan dilakukan saat buku diserahkan. Masa pinjam 7 hari dimulai setelah scan.</small></div><div class="handoff-token"><small>Token QR untuk input manual</small><code>{{ $loan->confirmation_token }}</code><button type="button" class="copy-qr-token" data-token="{{ $loan->confirmation_token }}" aria-label="Salin token QR">Salin token</button></div></div>
                @endif
                @if(!$isAdmin && $loan->confirmed_at)
                    <small class="loan-shelf-location"><strong>Lokasi pengambilan:</strong> {{ $loan->book->lokasi_rak ? 'Rak '.$loan->book->lokasi_rak : 'Lokasi rak belum diatur. Silakan tanyakan kepada petugas.' }}</small>
                @endif
                @if(!$isAdmin && $loan->fine)
                    @php($fineRates = \App\Actions\BookFinePolicy::DAMAGE_PERCENTAGES)
                    @php($conditionLabels = ['baik' => 'Baik', 'rusak_ringan' => 'Rusak ringan ('.$fineRates['rusak_ringan'].'%)', 'rusak_berat' => 'Rusak berat ('.$fineRates['rusak_berat'].'%)', 'hilang' => 'Hilang ('.$fineRates['hilang'].'%)'])
                    <div class="member-fine">
                        <strong>{{ $loan->fine->status_bayar === 'unpaid' ? 'Denda belum lunas' : 'Denda sudah lunas' }}: Rp{{ number_format($loan->fine->jumlah_denda, 0, ',', '.') }}</strong>
                        <small>Rincian: keterlambatan Rp{{ number_format($loan->denda_terlambat, 0, ',', '.') }} · {{ $conditionLabels[$loan->keterangan_kondisi] ?? 'Kondisi buku' }} Rp{{ number_format($loan->denda_tambahan, 0, ',', '.') }}.</small>
                        @if($loan->fine->status_bayar === 'unpaid')<small>Bayar ke petugas perpustakaan. Setelah pembayaran diterima, minta admin mencatat pelunasan di sistem.</small>@endif
                    </div>
                @endif
            </div>
            @if($loan->status === 'pending')
                <div class="loan-date loan-handoff-state"><small>Status serah-terima</small><strong>Menunggu scan admin</strong></div>
                <div class="loan-date loan-due-state"><small>Masa pinjam</small><strong>7 hari setelah scan</strong></div>
            @else
                <div class="loan-date"><small>Dipinjam</small><strong>{{ $loan->tanggal_pinjam->format('d M Y') }}</strong></div>
                <div class="loan-date"><small>Jatuh tempo</small><strong class="{{ $isLate ? 'late-text' : '' }}">{{ $loan->tenggat_kembali->format('d M Y') }}</strong></div>
            @endif
            @if($isAdmin)
                <div class="loan-admin-actions">
                    <span class="loan-status status-{{ $loan->status }}">{{ $isLate ? 'Terlambat' : ($loan->status === 'returned' && $loan->total_denda > 0 ? 'Dikembalikan + denda' : ucfirst($loan->status)) }}</span>
                    <div class="loan-admin-controls">
                        @if($loan->fine?->status_bayar === 'unpaid')
                            <form method="POST" action="{{ route('admin.fines.paid', $loan->fine, false) }}" class="fine-paid-form" onsubmit="return confirm('Pastikan pembayaran Rp{{ number_format($loan->fine->jumlah_denda, 0, ',', '.') }} sudah diterima. Tandai lunas?')">
                                @csrf @method('PATCH')
                                <button type="submit" class="button button-primary">Tandai denda lunas</button>
                            </form>
                        @elseif($loan->fine?->status_bayar === 'paid')
                            <span class="fine-paid-label">Denda lunas</span>
                        @endif
                        @if(in_array($loan->status, ['borrowed', 'overdue']))
                            @php($fineBookPrice = $loan->harga_buku ?? $loan->book->harga)
                            <details class="admin-return"><summary>Proses kembali</summary><form method="POST" action="{{ route('admin.loans.return.process', $loan, false) }}">@csrf
                                @php($fineRates = \App\Actions\BookFinePolicy::DAMAGE_PERCENTAGES)
                                <p class="field-hint">Harga acuan buku: <strong>{{ $fineBookPrice !== null ? 'Rp'.number_format($fineBookPrice, 0, ',', '.') : 'belum diatur' }}</strong>. Denda terlambat Rp{{ number_format(\App\Actions\BookFinePolicy::LATE_FINE_PER_DAY, 0, ',', '.') }} per hari. Denda kondisi: baik {{ $fineRates['baik'] }}%, rusak ringan {{ $fineRates['rusak_ringan'] }}%, rusak berat {{ $fineRates['rusak_berat'] }}%, hilang {{ $fineRates['hilang'] }}% dari harga acuan.</p>
                                @if($fineBookPrice === null)<p class="form-error">Atur harga buku sebelum memproses kondisi rusak atau hilang.</p><a class="button button-primary fine-price-link" href="{{ route('admin.books.edit', $loan->book) }}">Atur harga buku <span>↗</span></a>@endif
                                <label>Kondisi buku<select name="keterangan_kondisi" required><option value="">Pilih kondisi</option><option value="baik">Baik — tanpa denda kerusakan</option><option value="rusak_ringan" @disabled($fineBookPrice === null)>Rusak ringan — {{ $fineRates['rusak_ringan'] }}%</option><option value="rusak_berat" @disabled($fineBookPrice === null)>Rusak berat — {{ $fineRates['rusak_berat'] }}%</option><option value="hilang" @disabled($fineBookPrice === null)>Hilang — {{ $fineRates['hilang'] }}%</option></select></label>
                                <label>Catatan admin<textarea name="catatan_admin" rows="2" placeholder="Catatan kondisi buku"></textarea></label><button type="submit" class="button button-primary">Simpan pengembalian</button></form></details>
                        @endif
                        @if($loan->status !== 'returned')
                            <form method="POST" action="{{ route('admin.loans.status', $loan, false) }}" class="status-form">@csrf @method('PATCH')<select name="status" aria-label="Status {{ $loan->book->judul }}" onchange="this.form.submit()">@foreach(['pending', 'borrowed', 'overdue', 'rejected'] as $status)<option value="{{ $status }}" @selected($loan->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select></form>
                        @endif
                        <form method="POST" action="{{ route('admin.loans.destroy', $loan, false) }}" class="loan-delete-form" onsubmit="return confirm({{ Js::from('Hapus transaksi "'.$loan->book->judul.'" milik '.$loan->user->name.'? Stok buku disesuaikan dan denda terkait ikut terhapus.') }})">
                            @csrf @method('DELETE')
                            <button type="submit" class="loan-delete-button" aria-label="Hapus transaksi {{ $loan->book->judul }}">Hapus</button>
                        </form>
                    </div>
                </div>
            @else
                <span class="loan-status status-{{ $loan->status }} loan-member-status">{{ $isLate ? 'Terlambat' : ($loan->status === 'returned' && $loan->total_denda > 0 ? 'Dikembalikan + denda' : ucfirst($loan->status)) }}</span>
            @endif
        </article>
    @empty
        <div class="loan-empty"><span>✦</span><h3>Belum ada peminjaman</h3><p class="muted">Buku yang kamu pinjam akan muncul di sini.</p></div>
    @endforelse
</section>
@if(!$isAdmin)
<div id="loan-success-notice" class="loan-success-notice" hidden role="status" aria-live="polite">
    <section class="loan-success-dialog" aria-labelledby="loan-success-title">
        <button type="button" class="loan-success-close" aria-label="Tutup">&times;</button>
        <span class="loan-success-check" aria-hidden="true"><svg viewBox="0 0 52 52"><path d="M14 27l8 8 17-18" /></svg></span>
        <p class="loan-success-eyebrow">PEMINJAMAN TERCATAT</p>
        <h2 id="loan-success-title">Siap dibaca!</h2>
        <p class="loan-success-book"></p>
        <p class="loan-success-due"></p>
        <p class="loan-success-shelf"></p>
        <span class="loan-success-progress" aria-hidden="true"></span>
    </section>
</div>
@endif
@if($isAdmin && session('handoff_notice'))
    @php($handoffNotice = session('handoff_notice'))
    <div id="admin-handoff-notice" class="admin-handoff-notice" role="status" aria-live="polite">
        <section class="admin-handoff-dialog" aria-labelledby="admin-handoff-title">
            <button type="button" class="admin-handoff-close" aria-label="Tutup notifikasi">&times;</button>
            <span class="loan-success-check" aria-hidden="true"><svg viewBox="0 0 52 52"><path d="M14 27l8 8 17-18" /></svg></span>
            <p class="loan-success-eyebrow">SERAH-TERIMA TERCATAT</p>
            <h2 id="admin-handoff-title">Peminjaman dikonfirmasi</h2>
            <p class="admin-handoff-book">{{ $handoffNotice['book'] }}</p>
            <p class="admin-handoff-copy">{{ $handoffNotice['borrower'] }} · Jatuh tempo {{ $handoffNotice['due_date'] }}</p>
            <p class="admin-handoff-copy">{{ $handoffNotice['shelf_location'] ? 'Lokasi rak: '.$handoffNotice['shelf_location'] : 'Lokasi rak buku belum diatur.' }}</p>
            <span class="admin-handoff-progress" aria-hidden="true"></span>
        </section>
    </div>
@endif
@endsection