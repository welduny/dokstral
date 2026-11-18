<?php

namespace App\Http\Controllers;

use App\Actions\BuildMonthlyReportAction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AdminMonthlyReportController extends Controller
{
    public function __construct(private readonly BuildMonthlyReportAction $buildMonthlyReport)
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index(Request $request): View
    {
        return view('admin.reports.monthly', $this->reportViewData($request));
    }

    public function download(Request $request): Response
    {
        $data = $this->reportViewData($request);

        return Pdf::loadView('admin.reports.monthly-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->download('laporan-bulanan-'.$data['monthValue'].'.pdf');
    }

    private function reportViewData(Request $request): array
    {
        $timezone = config('app.timezone');
        $validated = $request->validate([
            'month' => [
                'nullable',
                'string',
                'date_format:Y-m',
                function (string $attribute, mixed $value, \Closure $fail) use ($timezone): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }

                    if ($value > CarbonImmutable::now($timezone)->format('Y-m')) {
                        $fail('Pilih bulan yang sudah berjalan atau selesai.');
                    }
                },
            ],
        ]);
        $monthValue = $validated['month'] ?? CarbonImmutable::now($timezone)->format('Y-m');
        $monthStart = CarbonImmutable::createFromFormat('!Y-m-d', $monthValue.'-01', $timezone);
        $report = $this->buildMonthlyReport->execute($monthStart);
        $periodEnd = CarbonImmutable::parse($report['period']['coverage_end'], $timezone)->locale('id');
        $monthLabel = $monthStart->locale('id')->translatedFormat('F Y');
        $rangeLabel = '1–'.$periodEnd->translatedFormat('j F Y');

        $definitions = [
            'started' => 'Dihitung dari konfirmasi serah-terima dalam bulan laporan. Reservasi yang belum diserahkan tidak termasuk.',
            'returned' => 'Dihitung dari tanggal pengembalian. Buku dapat berasal dari peminjaman bulan sebelumnya.',
            'legacy' => 'Peminjaman lama tanpa waktu konfirmasi menggunakan tanggal pinjam sebagai perkiraan.',
            'missing_returns' => 'Pengembalian tanpa tanggal tidak dapat ditempatkan pada bulan tertentu.',
            'issued_fines' => 'Denda yang tercatat pada pengembalian bertanggal di bulan laporan.',
            'paid_fines' => 'Pembayaran yang memiliki waktu pelunasan tercatat di bulan laporan.',
            'outstanding_fines' => 'Denda yang belum lunas pada akhir bulan, berdasarkan waktu pelunasan yang tercatat.',
            'unknown_paid' => 'Denda yang sudah lunas tetapi tidak memiliki waktu pelunasan; tidak dialokasikan ke bulan tertentu.',
            'borrowers' => 'Jumlah anggota siswa unik dengan peminjaman dimulai dalam bulan laporan.',
            'active_members' => 'Untuk bulan berjalan, dihitung dari status akun siswa saat ini. Riwayat status bulan sebelumnya tidak tersedia.',
            'new_members' => 'Akun siswa yang dibuat dalam bulan laporan.',
            'loan_details' => 'Daftar peminjaman yang diserahkan atau dikembalikan pada bulan laporan. Peminjaman yang belum kembali menampilkan durasi berjalan; kondisi dan denda dicatat saat pengembalian.',
        ];

        return [
            'report' => $report,
            'monthValue' => $monthValue,
            'monthLabel' => $monthLabel,
            'periodLabel' => ($report['period']['is_current_month'] ? 'Bulan berjalan · ' : $monthLabel.' · ').$rangeLabel,
            'pdfUrl' => route('admin.reports.monthly.pdf', ['month' => $monthValue]),
            'definitions' => $definitions,
            'hasActivity' => $report['circulation']['started_count'] > 0
                || $report['circulation']['returned_count'] > 0
                || $report['circulation']['returned_missing_date_count'] > 0
                || $report['fines']['issued_count'] > 0
                || $report['fines']['paid_count'] > 0
                || $report['fines']['outstanding_count'] > 0
                || $report['fines']['paid_without_timestamp_count'] > 0
                || $report['members']['borrowers_count'] > 0
                || $report['members']['active_members_count'] > 0
                || $report['members']['new_members_count'] > 0,
        ];
    }
}
