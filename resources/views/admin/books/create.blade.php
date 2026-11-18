@extends('layouts.app')

@section('content')
<section class="dashboard-head admin-page-head">
    <div><p class="eyebrow">AREA ADMIN</p><h1>Kelola koleksi.</h1><p class="muted">Tambah buku, catat harga pengganti, dan perbarui ketersediaan koleksi.</p></div>
    <div><a class="button button-primary" href="{{ route('admin.loans.scan') }}">Scan bukti pinjam <span>↗</span></a> <a class="button button-primary" href="{{ route('admin.loans.index') }}">Kelola peminjaman <span>↗</span></a></div>
</section>
<section class="admin-form-panel">
    <div class="admin-form-heading"><p class="eyebrow">DATA KOLEKSI</p><h2>Tambah buku</h2></div>
    <form method="POST" action="{{ route('admin.books.store', [], false) }}" class="book-create-form" enctype="multipart/form-data">
        @csrf
        <div class="admin-field"><label for="judul">Judul buku</label><input id="judul" name="judul" value="{{ old('judul') }}" required placeholder="Contoh: Filosofi Teras">@error('judul')<small>{{ $message }}</small>@enderror</div>
        <div class="admin-field"><label for="penulis">Penulis</label><input id="penulis" name="penulis" value="{{ old('penulis') }}" required placeholder="Nama penulis">@error('penulis')<small>{{ $message }}</small>@enderror</div>
        <div class="admin-field"><label for="isbn">ISBN <span>(opsional)</span></label><input id="isbn" name="isbn" value="{{ old('isbn') }}" placeholder="978-...">@error('isbn')<small>{{ $message }}</small>@enderror</div>
        <div class="admin-field"><label for="kategori">Kategori</label><select id="kategori" name="kategori"><option value="">Pilih kategori</option>@foreach(\App\Models\Book::CATEGORIES as $category)<option value="{{ $category }}" @selected(old('kategori') === $category)>{{ $category }}</option>@endforeach</select>@error('kategori')<small>{{ $message }}</small>@enderror</div>
        <div class="admin-field"><label for="harga">Harga buku (Rp)</label><input id="harga" type="number" name="harga" min="1" max="1000000000" step="1" inputmode="numeric" value="{{ old('harga') }}" required placeholder="Contoh: 85000">@error('harga')<small>{{ $message }}</small>@enderror<small class="field-hint">Patokan denda jika buku rusak atau hilang. Masukkan harga pengganti satu eksemplar.</small></div>
        <div class="admin-field"><label for="lokasi_rak">Lokasi rak</label><input id="lokasi_rak" name="lokasi_rak" value="{{ old('lokasi_rak') }}" maxlength="100" required placeholder="Contoh: A-01">@error('lokasi_rak')<small>{{ $message }}</small>@enderror<small class="field-hint">Lokasi ini akan ditampilkan kepada anggota setelah admin mengonfirmasi peminjaman.</small></div>
        <div class="admin-field"><label for="cover_file">Foto sampul <span>(opsional)</span></label><input id="cover_file" type="file" name="cover_file" accept="image/jpeg,image/png,image/webp">@error('cover_file')<small>{{ $message }}</small>@enderror<small class="field-hint">Pilih file JPG, PNG, atau WebP. Maksimal 2 MB.</small></div>
        <div class="admin-field"><label for="jumlah_total">Jumlah eksemplar</label><input id="jumlah_total" type="number" min="1" name="jumlah_total" value="{{ old('jumlah_total', 1) }}" required>@error('jumlah_total')<small>{{ $message }}</small>@enderror</div>
        <div class="admin-field admin-description-field"><label for="deskripsi">Deskripsi buku</label><textarea id="deskripsi" name="deskripsi" rows="4" maxlength="5000" placeholder="Ringkasan singkat isi buku">{{ old('deskripsi') }}</textarea>@error('deskripsi')<small>{{ $message }}</small>@enderror</div>
        <label class="admin-check"><input type="checkbox" name="is_circulating" value="1" @checked(old('is_circulating', true))><span>Buku dapat dipinjam oleh anggota</span></label>
        <button type="submit" class="button button-primary">Simpan buku <span>↗</span></button>
    </form>
</section>
<section class="admin-form-panel admin-book-list">
    <div class="admin-form-heading"><p class="eyebrow">KATALOG</p><h2>Kelola buku yang ada</h2></div>
    <div class="admin-book-list-items">
        @forelse($books as $book)
            <article class="admin-book-item">
                @if($book->cover_image_url)<img src="{{ $book->cover_image_url }}" alt="" loading="lazy">@else<span class="mini-cover">{{ strtoupper(substr($book->judul, 0, 1)) }}</span>@endif
                <div><strong>{{ $book->judul }}</strong><small>{{ $book->penulis }} · Rak {{ $book->lokasi_rak ?? 'belum diatur' }} · Rp{{ $book->harga !== null ? number_format($book->harga, 0, ',', '.') : 'Belum diatur' }} · Stok {{ $book->stok_tersedia }}/{{ $book->jumlah_total }}</small></div>
                <a class="button button-primary" href="{{ route('admin.books.edit', $book) }}">Edit buku <span>↗</span></a>
            </article>
        @empty
            <p class="muted">Belum ada buku di katalog.</p>
        @endforelse
    </div>
</section>
@endsection
