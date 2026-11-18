@extends('layouts.app')

@section('content')
<section class="dashboard-head admin-page-head">
    <div><p class="eyebrow">AREA ADMIN</p><h1>Edit buku.</h1><p class="muted">Perbarui detail, lokasi rak, harga pengganti, dan jumlah koleksi.</p></div>
    <a class="button button-primary" href="{{ route('admin.books.create') }}">Kembali ke koleksi <span>↗</span></a>
</section>
<section class="admin-form-panel">
    <div class="admin-form-heading"><p class="eyebrow">DATA KOLEKSI</p><h2>{{ $book->judul }}</h2></div>
    <form method="POST" action="{{ route('admin.books.update', $book, false) }}" class="book-create-form" enctype="multipart/form-data">
        @csrf @method('PATCH')
        <div class="admin-field"><label for="judul">Judul buku</label><input id="judul" name="judul" value="{{ old('judul', $book->judul) }}" required>@error('judul')<small>{{ $message }}</small>@enderror</div>
        <div class="admin-field"><label for="penulis">Penulis</label><input id="penulis" name="penulis" value="{{ old('penulis', $book->penulis) }}" required>@error('penulis')<small>{{ $message }}</small>@enderror</div>
        <div class="admin-field"><label for="isbn">ISBN</label><input id="isbn" name="isbn" value="{{ old('isbn', $book->isbn) }}">@error('isbn')<small>{{ $message }}</small>@enderror</div>
        <div class="admin-field"><label for="kategori">Kategori</label><select id="kategori" name="kategori"><option value="">Pilih kategori</option>@if($book->kategori && !in_array($book->kategori, \App\Models\Book::CATEGORIES, true))<option value="{{ $book->kategori }}" @selected(old('kategori', $book->kategori) === $book->kategori)>{{ $book->kategori }} (kategori lama)</option>@endif @foreach(\App\Models\Book::CATEGORIES as $category)<option value="{{ $category }}" @selected(old('kategori', $book->kategori) === $category)>{{ $category }}</option>@endforeach</select>@error('kategori')<small>{{ $message }}</small>@enderror</div>
        <div class="admin-field"><label for="harga">Harga buku (Rp)</label><input id="harga" type="number" name="harga" min="1" max="1000000000" step="1" inputmode="numeric" value="{{ old('harga', $book->harga) }}" required placeholder="Contoh: 85000">@error('harga')<small>{{ $message }}</small>@enderror<small class="field-hint">{{ $book->harga === null ? 'Buku lama belum memiliki harga. Isi harga pengganti sebelum memproses denda kerusakan atau kehilangan.' : 'Harga ini menjadi patokan denda kerusakan dan kehilangan.' }}</small></div>
        <div class="admin-field"><label for="lokasi_rak">Lokasi rak</label><input id="lokasi_rak" name="lokasi_rak" value="{{ old('lokasi_rak', $book->lokasi_rak) }}" maxlength="100" required placeholder="Contoh: A-01">@error('lokasi_rak')<small>{{ $message }}</small>@enderror<small class="field-hint">{{ $book->lokasi_rak === null ? 'Buku lama belum memiliki lokasi rak. Isi lokasi agar bisa diinformasikan kepada anggota setelah scan.' : 'Lokasi ini ditampilkan kepada anggota setelah admin mengonfirmasi peminjaman.' }}</small></div>
        <div class="admin-field">
            <label for="cover_file">Foto sampul</label>
            @if($book->cover_image_url)
                <span class="admin-cover-preview"><img src="{{ $book->cover_image_url }}" alt="Sampul saat ini: {{ $book->judul }}"><span>Cover saat ini akan tetap dipakai jika tidak ada file baru yang dipilih.</span></span>
            @endif
            <input id="cover_file" type="file" name="cover_file" accept="image/jpeg,image/png,image/webp">
            @error('cover_file')<small>{{ $message }}</small>@enderror
            <small class="field-hint">Pilih JPG, PNG, atau WebP. Maksimal 2 MB. Cover lama akan diganti setelah file baru disimpan.</small>
        </div>
        <div class="admin-field"><label for="jumlah_total">Jumlah eksemplar</label><input id="jumlah_total" type="number" min="1" max="100000" name="jumlah_total" value="{{ old('jumlah_total', $book->jumlah_total) }}" required>@error('jumlah_total')<small>{{ $message }}</small>@enderror<small class="field-hint">{{ $activeLoanCount }} eksemplar sedang direservasi atau dipinjam. Stok tersedia akan menyesuaikan perubahan jumlah.</small></div>
        <div class="admin-field admin-description-field"><label for="deskripsi">Deskripsi buku</label><textarea id="deskripsi" name="deskripsi" rows="5" maxlength="5000">{{ old('deskripsi', $book->deskripsi) }}</textarea>@error('deskripsi')<small>{{ $message }}</small>@enderror</div>
        <label class="admin-check"><input type="checkbox" name="is_circulating" value="1" @checked(old('is_circulating', $book->is_circulating))><span>Buku dapat dipinjam oleh anggota</span></label>
        <button type="submit" class="button button-primary">Simpan perubahan <span>↗</span></button>
    </form>
</section>
@endsection
