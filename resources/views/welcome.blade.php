@extends('layouts.app')

@section('content')
<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow">PERPUSTAKAAN DIGITAL SEKOLAH</p>
        <h1>Temukan buku.<br><em>Temukan arah.</em></h1>
        <p class="hero-text">Katalog yang rapi untuk membantu kamu menemukan bacaan yang tepat, meminjamnya dengan mudah, dan terus bertumbuh setiap hari.</p>
        <div class="hero-actions"><a href="#katalog" class="button button-primary">Jelajahi koleksi <span>↗</span></a><a href="{{ route('register') }}" class="text-link">Buat akun baru</a></div>
    </div>
    <div class="hero-art" aria-hidden="true"><div class="art-sun"></div><div class="art-book book-one"></div><div class="art-book book-two"></div><div class="art-book book-three"></div><span class="art-note">Baca<br>lebih<br>jauh.</span></div>
</section>

<section class="catalog-section" id="katalog">
    <div class="section-heading"><div><p class="eyebrow">PILIHAN MINGGU INI</p><h2>Buku yang sedang ramai dibaca</h2></div><span class="section-count">{{ $books->count() }} koleksi terbaru</span></div>
    <div class="book-grid">
        @forelse($books as $book)
            <article class="book-card">
                <div class="book-cover cover-{{ $loop->iteration % 4 }}"><span>{{ strtoupper(substr($book->judul, 0, 1)) }}</span></div>
                <div class="book-info">
                    <h3>{{ $book->judul }}</h3><p>{{ $book->penulis }}</p>
                    <span class="availability">{{ $book->stok_tersedia > 0 ? 'Tersedia ('.$book->stok_tersedia.')' : 'Stok habis' }}</span>
                    @auth
                        @if($book->stok_tersedia > 0)
                            <details class="loan-confirm"><summary class="button button-primary">Pinjam <span>↗</span></summary><form method="POST" action="{{ route('loans.store', $book) }}" class="loan-form">@csrf<div class="loan-rules"><strong>Konfirmasi peminjaman</strong><p>Kamu akan meminjam buku ini sampai <b>{{ now()->addDays(7)->format('d/m/Y') }}</b>.</p><ul><li>Maksimal 3 buku aktif dalam satu waktu.</li><li>Akun tidak boleh memiliki denda atau buku overdue.</li><li>Buku wajib dikembalikan sesuai tanggal jatuh tempo.</li><li>Stok dikunci saat konfirmasi; peminjaman gagal jika stok berubah menjadi habis.</li></ul></div><label class="loan-agreement"><input type="checkbox" name="accept_rules" value="1" required> <span>Saya memahami dan menyetujui ketentuan peminjaman.</span></label><button type="submit" class="button button-primary">Konfirmasi pinjam <span>↗</span></button></form></details>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="book-login-link">Masuk untuk meminjam</a>
                    @endauth
                </div>
            </article>
        @empty
            <p class="muted">Belum ada buku di katalog.</p>
        @endforelse
    </div>
</section>

<section class="quote-band"><p>“Sebuah buku adalah cara untuk berbicara dengan seseorang yang tidak bisa kita temui.”</p><span>— Carl Sagan</span></section>
@endsection