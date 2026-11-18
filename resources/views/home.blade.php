@extends('layouts.app')

@section('content')
<section class="dashboard-head">
    <div><p class="eyebrow">RUANG PRIBADI</p><h1>Selamat datang, {{ Auth::user()->name }}.</h1><p class="muted">Pantau aktivitas bacamu dan temukan bacaan berikutnya.</p></div>
    <a class="button button-primary" href="{{ url('/') }}#katalog">Cari buku <span>↗</span></a>
</section>
<section class="stats-grid">
    <div class="stat-card"><span class="stat-icon">▦</span><div><strong>{{ $bookCount }}</strong><span>Total koleksi</span></div></div>
    <div class="stat-card"><span class="stat-icon warm">✦</span><div><strong>{{ $availableCount }}</strong><span>Buku tersedia</span></div></div>
    <div class="stat-card"><span class="stat-icon green">↗</span><div><strong>{{ $activeBorrowings }}</strong><span>Peminjaman aktif</span></div></div>
</section>
<section class="dashboard-content">
    <div class="panel">
        <div class="panel-heading"><div><p class="eyebrow">KOLEKSI TERBARU</p><h2>Mulai dari sini</h2></div><a href="{{ url('/') }}#katalog" class="text-link">Lihat katalog ↗</a></div>
        <div class="mini-list">
            @forelse($recentBooks as $book)
                <a class="mini-book" href="{{ url('/') }}#katalog">
                    <span class="mini-cover">{{ strtoupper(substr($book->judul, 0, 1)) }}</span>
                    <span class="mini-book-copy"><strong>{{ $book->judul }}</strong><small>{{ $book->penulis }}</small></span>
                    <span class="availability">{{ $book->stok_tersedia > 0 ? 'Tersedia' : 'Habis' }}</span>
                </a>
            @empty
                <p class="muted">Belum ada buku di katalog.</p>
            @endforelse
        </div>
    </div>
    <aside class="reading-challenge">
        <p class="eyebrow">TANTANGAN {{ strtoupper(now()->translatedFormat('F')) }}</p>
        <h2>{{ min($booksRead, $challenge->target) }} <span>/ {{ $challenge->target }} buku</span></h2>
        <div class="challenge-track" role="progressbar" aria-label="Progres tantangan baca" aria-valuemin="0" aria-valuemax="{{ $challenge->target }}" aria-valuenow="{{ min($booksRead, $challenge->target) }}"><span style="width:{{ min(100, (int) round($booksRead / max(1, $challenge->target) * 100)) }}%"></span></div>
        <p class="muted">Buku yang selesai dikembalikan bulan ini: {{ $booksRead }}.</p>
        <form method="POST" action="{{ route('reading-challenge.update', [], false) }}" class="challenge-form">@csrf @method('PUT')<label for="reading-target">Target bulanan</label><div><input id="reading-target" type="number" name="target" min="1" max="50" value="{{ $challenge->target }}" required><button class="button button-primary" type="submit">Simpan target</button></div></form>
    </aside>
</section>
@if($dueReminders->isNotEmpty())
<section class="due-reminders" aria-labelledby="reminder-heading">
    <div class="panel-heading"><div><p class="eyebrow">PENGINGAT</p><h2 id="reminder-heading">Jatuh tempo mendekat</h2></div><span class="section-count">{{ $dueReminders->count() }} pengingat</span></div>
    <div class="reminder-list">
        @foreach($dueReminders as $notification)
            <a class="reminder-item" href="{{ route('loans.mine') }}"><span class="reminder-mark">!</span><span><strong>{{ $notification->data['book_title'] }}</strong><small>{{ $notification->data['message'] }} · {{ \Illuminate\Support\Carbon::parse($notification->data['due_date'])->format('d M Y') }}</small></span><span aria-hidden="true">↗</span></a>
        @endforeach
    </div>
</section>
@endif
<section class="tip-panel"><span class="tip-mark">✦</span><p class="eyebrow">CATATAN HARI INI</p><h3>Jadwalkan waktu membaca 20 menit.</h3><p>Perjalanan yang panjang selalu dimulai dari satu halaman.</p></section>
@endsection
