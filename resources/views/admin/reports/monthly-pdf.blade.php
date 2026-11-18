<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan bulanan {{ $monthLabel }}</title>
    <style>
        @page { margin: 34px 38px; }
        body { margin: 0; color: #18352d; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.5; }
        h1 { margin: 0 0 5px; font-family: DejaVu Serif, serif; font-size: 24px; }
        h2 { margin: 0 0 4px; font-family: DejaVu Serif, serif; font-size: 15px; }
        p { margin: 0 0 9px; }
        .period { margin-bottom: 22px; color: #718078; font-size: 11px; }
        .section { margin: 0 0 22px; page-break-inside: avoid; }
        .definition { margin-bottom: 8px; color: #52645a; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 9px; border-bottom: 1px solid #e3e7df; vertical-align: top; }
        th { color: #718078; font-size: 9px; font-weight: normal; text-align: left; }
        td.amount, th.amount { text-align: right; white-space: nowrap; }
        .total { font-size: 13px; font-weight: bold; }
        .note { margin-top: 8px; padding: 8px 9px; color: #52645a; background: #f7f6f0; }
        .empty { margin: 0 0 16px; padding: 9px; color: #52645a; background: #f7f6f0; }
        .details-section { margin: 0 0 22px; }
        .details-section h2, .details-section .definition { page-break-after: avoid; }
        .details-table { font-size: 8px; }
        .details-table thead { display: table-header-group; }
        .details-table tr { page-break-inside: avoid; }
        .details-table th, .details-table td { padding: 6px 5px; }
        .details-table th { background: #f7f6f0; }
        .details-table small { color: #718078; font-size: 7px; }
        .details-table .amount { white-space: nowrap; }
        .footer { margin-top: 22px; padding-top: 8px; border-top: 1px solid #e3e7df; color: #718078; font-size: 8px; }
    </style>
</head>
<body>
    <h1>Laporan bulanan</h1>
    <p class="period">{{ $periodLabel }}</p>

    @if(!$hasActivity)
        <p class="empty">Belum ada aktivitas pada bulan ini.</p>
    @endif

    <section class="section">
        <h2>Peminjaman</h2>
        <p class="definition">{{ $definitions['started'] }}</p>
        <table>
            <tbody>
                <tr><td>Peminjaman dimulai</td><td class="amount total">{{ number_format($report['circulation']['started_count'], 0, ',', '.') }}</td></tr>
                <tr><td>Buku dikembalikan</td><td class="amount total">{{ number_format($report['circulation']['returned_count'], 0, ',', '.') }}</td></tr>
            </tbody>
        </table>
        @if($report['circulation']['started_legacy_count'] > 0)
            <p class="note">{{ number_format($report['circulation']['started_legacy_count'], 0, ',', '.') }} peminjaman lama dihitung dari tanggal pinjam karena waktu konfirmasi belum tersedia.</p>
        @endif
        @if($report['circulation']['returned_missing_date_count'] > 0)
            <p class="note">{{ number_format($report['circulation']['returned_missing_date_count'], 0, ',', '.') }} pengembalian tidak memiliki tanggal kembali dan tidak dimasukkan ke jumlah bulanan. {{ $definitions['missing_returns'] }}</p>
        @endif
        @if($report['period']['month_value'] <= '2026-10')
            <p class="note">Status peminjaman sebelum pencatatan konfirmasi tersedia tidak memiliki riwayat perubahan lengkap.</p>
        @endif
    </section>

    <section class="section">
        <h2>Denda</h2>
        <p class="definition">Jumlah rupiah menggunakan data denda yang tercatat pada sistem.</p>
        <table>
            <thead><tr><th>Rincian</th><th class="amount">Jumlah denda</th><th class="amount">Nilai</th></tr></thead>
            <tbody>
                <tr><td>Denda ditetapkan<br>{{ $definitions['issued_fines'] }}</td><td class="amount">{{ number_format($report['fines']['issued_count'], 0, ',', '.') }}</td><td class="amount">Rp{{ number_format($report['fines']['issued_amount'], 0, ',', '.') }}</td></tr>
                <tr><td>Denda dibayar<br>{{ $definitions['paid_fines'] }}</td><td class="amount">{{ number_format($report['fines']['paid_count'], 0, ',', '.') }}</td><td class="amount">Rp{{ number_format($report['fines']['paid_amount'], 0, ',', '.') }}</td></tr>
                <tr><td>Belum lunas pada akhir bulan<br>{{ $definitions['outstanding_fines'] }}</td>@if($report['fines']['outstanding_history_unavailable'])<td class="amount" colspan="2">Data belum tersedia</td>@else<td class="amount">{{ number_format($report['fines']['outstanding_count'], 0, ',', '.') }}</td><td class="amount">Rp{{ number_format($report['fines']['outstanding_amount'], 0, ',', '.') }}</td>@endif</tr>
            </tbody>
        </table>
        @if($report['fines']['paid_without_timestamp_count'] > 0)
            <p class="note">{{ number_format($report['fines']['paid_without_timestamp_count'], 0, ',', '.') }} denda (Rp{{ number_format($report['fines']['paid_without_timestamp_amount'], 0, ',', '.') }}) sudah lunas tetapi belum memiliki tanggal pembayaran. {{ $definitions['unknown_paid'] }}</p>
        @endif
    </section>

    <section class="section">
        <h2>Anggota</h2>
        <p class="definition">Anggota siswa yang tercatat pada bulan laporan.</p>
        <table>
            <tbody>
                <tr><td>Anggota yang meminjam<br>{{ $definitions['borrowers'] }}</td><td class="amount total">{{ number_format($report['members']['borrowers_count'], 0, ',', '.') }}</td></tr>
                <tr><td>Anggota aktif<br>{{ $definitions['active_members'] }}</td><td class="amount total">{{ $report['members']['active_members_count'] === null ? 'Data belum tersedia' : number_format($report['members']['active_members_count'], 0, ',', '.') }}</td></tr>
                <tr><td>Anggota baru<br>{{ $definitions['new_members'] }}</td><td class="amount total">{{ number_format($report['members']['new_members_count'], 0, ',', '.') }}</td></tr>
            </tbody>
        </table>
    </section>

    <section class="details-section">
        <h2>Rincian peminjaman</h2>
        <p class="definition">{{ $definitions['loan_details'] }}</p>
        @if($report['loan_details']->isEmpty())
            <p class="empty">Belum ada peminjaman yang diserahkan atau dikembalikan pada bulan ini.</p>
        @else
            <table class="details-table">
                <thead>
                    <tr>
                        <th>Peminjam / buku</th>
                        <th>Pinjam – kembali / hari</th>
                        <th>Kondisi</th>
                        <th class="amount">Denda</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['loan_details'] as $detail)
                        <tr>
                            <td>{{ $detail['borrower'] }}<br><small>{{ $detail['book'] }}</small></td>
                            <td>{{ $detail['borrowed_on']->locale('id')->translatedFormat('d M Y') }}<br>– {{ $detail['returned_on']?->locale('id')->translatedFormat('d M Y') ?? 'Belum kembali' }}<br><small>{{ number_format($detail['duration_days'], 0, ',', '.') }} hari{{ $detail['returned_on'] ? '' : ' berjalan' }}<br>{{ $detail['status_label'] }}</small></td>
                            <td>{{ $detail['condition'] }}</td>
                            <td class="amount">{{ $detail['fine_amount'] === null ? 'Belum dihitung' : 'Rp'.number_format($detail['fine_amount'], 0, ',', '.') }}<br><small>{{ $detail['fine_status'] }}@if($detail['late_fine'] > 0 || $detail['extra_fine'] > 0)<br>Telat Rp{{ number_format($detail['late_fine'], 0, ',', '.') }} · Kondisi buku Rp{{ number_format($detail['extra_fine'], 0, ',', '.') }}@endif</small></td>
                            <td>{{ $detail['notes'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <p class="footer">LEHBOOK · Cakupan laporan: {{ $periodLabel }}</p>
</body>
</html>
