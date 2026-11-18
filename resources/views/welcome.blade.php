@extends('layouts.app')

@section('content')
<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow">PERPUSTAKAAN DIGITAL SEKOLAH</p>
        <h1>Temukan buku.<br><em>Temukan arah.</em></h1>
        <p class="hero-text">Cari bacaan baru, simpan yang menarik, dan jelajahi cerita di balik setiap sampul.</p>
        <div class="hero-actions"><a href="#katalog" class="button button-primary">Jelajahi koleksi <span>↗</span></a>@guest<a href="{{ route('register') }}" class="text-link">Buat akun baru</a>@endguest</div>
    </div>
    <div class="hero-art" aria-hidden="true"><div class="art-sun"></div><div class="art-book book-one"></div><div class="art-book book-two"></div><div class="art-book book-three"></div><span class="art-note">Baca<br>lebih<br>jauh.</span></div>
</section>

<section class="catalog-section" id="katalog">
    <div class="section-heading"><div><p class="eyebrow">JELAJAHI RAK</p><h2>Temukan bacaanmu</h2></div><span class="section-count">{{ $books->total() }} koleksi</span></div>
    <form class="catalog-filters" method="GET" action="{{ route('catalog', [], false) }}">
        <label class="catalog-search"><span aria-hidden="true">⌕</span><input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari judul, penulis, atau ISBN" aria-label="Cari buku"></label>
        <label class="filter-select"><span class="sr-only">Kategori</span><select name="category"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>@endforeach</select></label>
        <label class="filter-availability"><input type="checkbox" name="availability" value="available" @checked(($filters['availability'] ?? '') === 'available')><span>Tersedia saja</span></label>
        <button class="button button-primary" type="submit">Terapkan filter <span>↗</span></button>
        @if(count($filters))<a class="catalog-reset" href="{{ route('catalog', [], false) }}">Reset</a>@endif
    </form>
    <div class="book-grid">
        @forelse($books as $book)
            <article class="book-card" tabindex="0" data-detail-trigger="book-detail-{{ $book->id }}" aria-label="Lihat detail {{ $book->judul }}">
                <button type="button" class="book-detail-trigger" aria-haspopup="dialog" aria-controls="book-detail-{{ $book->id }}">
                    <span class="book-cover cover-{{ $loop->iteration % 4 }}">
                        @if($book->cover_image_url)
                            <img src="{{ $book->cover_image_url }}" alt="Sampul {{ $book->judul }}" loading="lazy">
                            <span class="cover-fallback" hidden>{{ strtoupper(substr($book->judul, 0, 1)) }}</span>
                        @else
                            <span>{{ strtoupper(substr($book->judul, 0, 1)) }}</span>
                        @endif
                    </span>
                    <span class="book-info">
                        <strong class="book-title">{{ $book->judul }}</strong>
                        <span class="book-author">{{ $book->penulis }}</span>
                        <span class="availability">{{ $book->is_circulating && $book->stok_tersedia > 0 ? 'Tersedia · '.$book->stok_tersedia : 'Tidak tersedia' }}</span>
                        <span class="book-rating">@if($book->reviews_count)<span aria-hidden="true">★</span> {{ number_format((float)$book->reviews_avg_rating, 1) }} <small>({{ $book->reviews_count }})</small>@else <span aria-hidden="true">☆</span> Belum ada ulasan @endif</span>
                    </span>
                </button>
                @auth
                    <div class="book-actions">
                        <form method="POST" action="{{ route('books.favorite', $book, false) }}" class="favorite-form">@csrf<button type="submit" class="favorite-toggle {{ in_array($book->id, $favoriteBookIds, true) ? 'is-favorite' : '' }}" aria-label="{{ in_array($book->id, $favoriteBookIds, true) ? 'Hapus dari favorit' : 'Simpan ke favorit' }}" aria-pressed="{{ in_array($book->id, $favoriteBookIds, true) ? 'true' : 'false' }}">{{ in_array($book->id, $favoriteBookIds, true) ? '♥' : '♡' }}</button></form>
                        @if(Auth::user()->role === 'admin')
                            <a class="book-admin-link" href="{{ route('admin.books.edit', $book, false) }}">Kelola buku <span aria-hidden="true">↗</span></a>
                        @elseif($book->is_circulating && $book->stok_tersedia > 0)
                            <details class="loan-confirm"><summary class="button button-primary">Pinjam <span>↗</span></summary><form method="POST" action="{{ route('loans.store', $book, false) }}" class="loan-form">@csrf<div class="loan-rules"><strong>Konfirmasi peminjaman</strong><p>Masa pinjam 7 hari dimulai setelah admin memindai QR saat buku diserahkan.</p><ul><li>Maksimal 3 buku aktif.</li><li>Akun tidak boleh punya denda atau buku terlambat.</li><li>Stok dipesan setelah reservasi dikonfirmasi.</li></ul></div><label class="loan-agreement"><input type="checkbox" name="accept_rules" value="1" required> <span>Saya setuju dengan ketentuan peminjaman.</span></label><button type="submit" class="button button-primary">Konfirmasi pinjam <span>↗</span></button></form></details>
                        @endif
                    </div>
                @else
                    <a href="{{ route('login') }}" class="book-login-link">Masuk untuk meminjam</a>
                @endauth
                <dialog class="book-detail-dialog" id="book-detail-{{ $book->id }}" aria-labelledby="book-title-{{ $book->id }}">
                    <button type="button" class="book-detail-close" aria-label="Tutup detail">&times;</button>
                    <div class="book-detail-layout">
                        <div class="book-detail-cover cover-{{ $loop->iteration % 4 }}">
                            @if($book->cover_image_url)
                                <img src="{{ $book->cover_image_url }}" alt="Sampul {{ $book->judul }}" loading="lazy">
                                <span class="cover-fallback" hidden>{{ strtoupper(substr($book->judul, 0, 1)) }}</span>
                            @else
                                <span>{{ strtoupper(substr($book->judul, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="book-detail-copy">
                            <p class="eyebrow">{{ $book->kategori ?: 'KOLEKSI LEHBOOK' }}</p>
                            <h2 id="book-title-{{ $book->id }}">{{ $book->judul }}</h2>
                            <p class="book-detail-author">{{ $book->penulis }}</p>
                            <p class="book-detail-availability">{{ $book->is_circulating && $book->stok_tersedia > 0 ? 'Tersedia · '.$book->stok_tersedia.' eksemplar' : 'Sedang tidak tersedia' }}</p>
                            <p class="book-description">{{ $book->deskripsi ?: 'Deskripsi buku belum ditambahkan.' }}</p>
                            @if($book->isbn)<p class="book-isbn">ISBN {{ $book->isbn }}</p>@endif
                            <div class="book-detail-reviews">
                                <strong>Ulasan pembaca</strong>
                                @if($book->reviews_count)<span>★ {{ number_format((float)$book->reviews_avg_rating, 1) }} / 5 · {{ $book->reviews_count }} ulasan</span>@else<span>Belum ada ulasan</span>@endif
                                @foreach($book->reviews as $review)
                                    <blockquote><span aria-label="{{ $review->rating }} dari 5 bintang">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>@if($review->review)<p>{{ $review->review }}</p>@endif<small>{{ $review->user->name }}</small></blockquote>
                                @endforeach
                            </div>
                            @auth
                                @if(in_array($book->id, $reviewableBookIds, true))
                                    <form method="POST" action="{{ route('books.reviews.store', $book, false) }}" class="book-review-form">@csrf<label for="rating-{{ $book->id }}">Rating</label><select id="rating-{{ $book->id }}" name="rating" required><option value="">Pilih rating</option>@for($rating=5;$rating>=1;$rating--)<option value="{{ $rating }}">{{ $rating }} bintang</option>@endfor</select><label for="review-{{ $book->id }}">Ulasan singkat</label><textarea id="review-{{ $book->id }}" name="review" maxlength="1000" rows="3" placeholder="Apa kesanmu setelah membaca?"></textarea><button class="button button-primary" type="submit">Simpan ulasan <span>↗</span></button></form>
                                @endif
                            @endauth
                        </div>
                    </div>
                </dialog>
            </article>
        @empty
            <p class="muted catalog-empty">Tidak ada buku yang cocok. Coba ubah kata pencarian atau filternya.</p>
        @endforelse
    </div>
    <div class="catalog-pagination">{{ $books->links() }}</div>
</section>
<section class="quote-band"><p>“Sebuah buku adalah cara untuk berbicara dengan seseorang yang tidak bisa kita temui.”</p><span>— Carl Sagan</span></section>
@endsection
