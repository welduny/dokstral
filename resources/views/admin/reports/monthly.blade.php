@extends('layouts.app')

@section('content')
<section class="dashboard-head admin-page-head monthly-report-head">
    <div>
        <h1>Laporan bulanan</h1>
        <p class="muted">Ringkasan peminjaman, denda, dan anggota untuk bulan yang dipilih.</p>
    </div>
</section>

<section class="monthly-report-toolbar" aria-label="Periode laporan">
    <div class="monthly-report-period">
        <form class="monthly-report-filter" method="GET" action="{{ route('admin.reports.monthly') }}">
            <label for="report-month">Bulan laporan</label>
            <div class="monthly-report-controls">
                <input id="report-month" name="month" type="month" value="{{ $monthValue }}" max="{{ now(config('app.timezone'))->format('Y-m') }}" data-applied-month="{{ $monthValue }}" required>
                <button class="button button-primary" type="submit">Tampilkan laporan</button>
            </div>
            <small id="report-month-hint" class="monthly-report-hint" hidden>Tampilkan laporan untuk menerapkan bulan ini.</small>
        </form>
        <p class="monthly-report-period-label">{{ $periodLabel }}</p>
    </div>
    <a class="button monthly-report-download" href="{{ $pdfUrl }}" aria-label="Unduh PDF laporan {{ $monthLabel }}">Unduh PDF</a>
</section>

<section class="monthly-report-sheet" aria-label="Ringkasan operasional {{ $monthLabel }}">
    @if(!$hasActivity)
        <p class="monthly-report-empty">Belum ada aktivitas pada bulan ini.</p>
    @endif

    <section class="monthly-report-section monthly-report-circulation" aria-labelledby="report-circulation-title">
        <div class="monthly-report-section-heading">
            <h2 id="report-circulation-title">Peminjaman</h2>
            <p>{{ $definitions['started'] }}</p>
        </div>
        <dl class="monthly-report-metrics">
            <div class="monthly-report-metric monthly-report-metric-primary">
                <dt>Peminjaman dimulai</dt>
                <dd>{{ number_format($report['circulation']['started_count'], 0, ',', '.') }}</dd>
            </div>
            <div class="monthly-report-metric">
                <dt>Buku dikembalikan</dt>
                <dd>{{ number_format($report['circulation']['returned_count'], 0, ',', '.') }}</dd>
            </div>
        </dl>
        @if($report['circulation']['started_legacy_count'] > 0)
            <p class="monthly-report-note">{{ number_format($report['circulation']['started_legacy_count'], 0, ',', '.') }} peminjaman lama dihitung dari tanggal pinjam karena waktu konfirmasi belum tersedia.</p>
        @endif
        @if($report['circulation']['returned_missing_date_count'] > 0)
            <p class="monthly-report-note">{{ number_format($report['circulation']['returned_missing_date_count'], 0, ',', '.') }} pengembalian tidak memiliki tanggal kembali dan tidak dimasukkan ke jumlah bulanan. {{ $definitions['missing_returns'] }}</p>
        @endif
        @if($report['period']['month_value'] <= '2026-10')
            <p class="monthly-report-note">Status peminjaman sebelum pencatatan konfirmasi tersedia tidak memiliki riwayat perubahan lengkap.</p>
        @endif
    </section>

    <section class="monthly-report-section" aria-labelledby="report-fines-title">
        <div class="monthly-report-section-heading">
            <h2 id="report-fines-title">Denda</h2>
            <p>Jumlah rupiah menggunakan data denda yang tercatat pada sistem.</p>
        </div>
        <dl class="monthly-report-ledger">
            <div><dt>Denda ditetapkan <small>{{ $definitions['issued_fines'] }}</small></dt><dd><span>{{ number_format($report['fines']['issued_count'], 0, ',', '.') }} denda</span><strong>Rp{{ number_format($report['fines']['issued_amount'], 0, ',', '.') }}</strong></dd></div>
            <div><dt>Denda dibayar <small>{{ $definitions['paid_fines'] }}</small></dt><dd><span>{{ number_format($report['fines']['paid_count'], 0, ',', '.') }} denda</span><strong>Rp{{ number_format($report['fines']['paid_amount'], 0, ',', '.') }}</strong></dd></div>
            <div><dt>Belum lunas pada akhir bulan <small>{{ $definitions['outstanding_fines'] }}</small></dt><dd>@if($report['fines']['outstanding_history_unavailable'])<strong>Data belum tersedia</strong>@else<span>{{ number_format($report['fines']['outstanding_count'], 0, ',', '.') }} denda</span><strong>Rp{{ number_format($report['fines']['outstanding_amount'], 0, ',', '.') }}</strong>@endif</dd></div>
        </dl>
        @if($report['fines']['paid_without_timestamp_count'] > 0)
            <p class="monthly-report-note">{{ number_format($report['fines']['paid_without_timestamp_count'], 0, ',', '.') }} denda (Rp{{ number_format($report['fines']['paid_without_timestamp_amount'], 0, ',', '.') }}) sudah lunas tetapi belum memiliki tanggal pembayaran. {{ $definitions['unknown_paid'] }}</p>
        @endif
    </section>

    <section class="monthly-report-section monthly-report-members" aria-labelledby="report-members-title">
        <div class="monthly-report-section-heading">
            <h2 id="report-members-title">Anggota</h2>
            <p>Anggota siswa yang tercatat pada bulan laporan.</p>
        </div>
        <dl class="monthly-report-ledger">
            <div><dt>Anggota yang meminjam <small>{{ $definitions['borrowers'] }}</small></dt><dd><strong>{{ number_format($report['members']['borrowers_count'], 0, ',', '.') }}</strong></dd></div>
            <div><dt>Anggota aktif <small>{{ $definitions['active_members'] }}</small></dt><dd><strong>{{ $report['members']['active_members_count'] === null ? 'Data belum tersedia' : number_format($report['members']['active_members_count'], 0, ',', '.') }}</strong></dd></div>
            <div><dt>Anggota baru <small>{{ $definitions['new_members'] }}</small></dt><dd><strong>{{ number_format($report['members']['new_members_count'], 0, ',', '.') }}</strong></dd></div>
        </dl>
    </section>

    <section class="monthly-report-section" aria-labelledby="report-loan-details-title">
        <div class="monthly-report-section-heading">
            <h2 id="report-loan-details-title">Rincian peminjaman</h2>
            <p>{{ $definitions['loan_details'] }}</p>
        </div>

        @if($report['loan_details']->isEmpty())
            <p class="monthly-report-empty">Belum ada peminjaman yang diserahkan atau dikembalikan pada bulan ini.</p>
        @else
            <div class="monthly-report-table-wrap" role="region" aria-label="Rincian pengembalian bulan {{ $monthLabel }}; geser untuk melihat semua kolom" tabindex="0">
                <table class="monthly-report-table">
                    <caption>{{ number_format($report['loan_details']->count(), 0, ',', '.') }} transaksi pengembalian</caption>
                    <thead>
                        <tr>
                            <th scope="col">Peminjam / buku</th>
                            <th scope="col">Pinjam – kembali / hari</th>
                            <th scope="col">Kondisi / kerusakan</th>
                            <th scope="col">Denda</th>
                            <th scope="col">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['loan_details'] as $detail)
                            <tr>
                                <td><strong>{{ $detail['borrower'] }}</strong><small>{{ $detail['book'] }}</small></td>
                                <td>
                                    <strong>{{ $detail['borrowed_on']->locale('id')->translatedFormat('d M Y') }} – {{ $detail['returned_on']?->locale('id')->translatedFormat('d M Y') ?? 'Belum kembali' }}</strong>
                                    <small>{{ number_format($detail['duration_days'], 0, ',', '.') }} hari{{ $detail['returned_on'] ? '' : ' berjalan' }}</small>
                                    <small>{{ $detail['status_label'] }}</small>
                                </td>
                                <td>{{ $detail['condition'] }}</td>
                                <td class="money">
                                    <strong>{{ $detail['fine_amount'] === null ? 'Belum dihitung' : 'Rp'.number_format($detail['fine_amount'], 0, ',', '.') }}</strong>
                                    <small>{{ $detail['fine_status'] }}</small>
                                    @if($detail['late_fine'] > 0 || $detail['extra_fine'] > 0)
                                        <small>Telat Rp{{ number_format($detail['late_fine'], 0, ',', '.') }} · Kondisi buku Rp{{ number_format($detail['extra_fine'], 0, ',', '.') }}</small>
                                    @endif
                                </td>
                                <td>{{ $detail['notes'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="monthly-report-table-hint">Geser tabel ke samping untuk melihat semua kolom.</p>
        @endif
    </section>
</section>

<script>
    const reportMonth = document.querySelector('#report-month');
    const reportMonthHint = document.querySelector('#report-month-hint');

    if (reportMonth && reportMonthHint) {
        reportMonth.addEventListener('input', () => {
            reportMonthHint.hidden = reportMonth.value === reportMonth.dataset.appliedMonth;
        });
    }
</script>
@endsection
