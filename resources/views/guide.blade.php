@extends('layouts.app')

@section('content')
<style>
    .guide-page { padding:48px 0 78px; }
    .guide-hero { position:relative; overflow:hidden; padding:46px; color:#fffefa; background:#18352d; }
    .guide-hero::after { position:absolute; right:40px; bottom:-110px; width:270px; height:270px; border:1px solid rgba(247,200,121,.38); border-radius:50%; content:""; box-shadow:0 0 0 24px rgba(247,200,121,.05),0 0 0 50px rgba(247,200,121,.04); }
    .guide-hero .eyebrow { color:#f7c879; }
    .guide-hero h1 { position:relative; z-index:1; max-width:620px; margin-bottom:14px; font-size:clamp(38px,6vw,62px); }
    .guide-hero p:last-child { position:relative; z-index:1; max-width:520px; margin:0; color:#c7d2ca; font-size:14px; line-height:1.75; }
    .guide-contents { display:flex; flex-wrap:wrap; gap:9px; margin:20px 0 36px; }
    .guide-contents a { padding:9px 13px; border:1px solid #d8e0d9; color:#315f43; background:#fffefa; font-size:12px; font-weight:700; }
    .guide-contents a:hover { border-color:#18352d; color:#18352d; }
    .guide-section { margin-top:42px; scroll-margin-top:24px; }
    .guide-section-heading { display:flex; align-items:end; justify-content:space-between; gap:16px; margin-bottom:18px; }
    .guide-section-heading .eyebrow { margin-bottom:8px; }
    .guide-section-heading h2 { font-size:30px; }
    .guide-section-heading > span { color:#718078; font-size:12px; }
    .guide-steps { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
    .guide-step { min-height:176px; padding:22px; background:#fffefa; border:1px solid #e3e7df; }
    .guide-step-number { display:grid; place-items:center; width:34px; height:34px; margin-bottom:17px; color:#8b3f30; background:#fff0eb; font:700 14px 'DM Sans',sans-serif; }
    .guide-step h3 { margin:0 0 8px; color:#18352d; font:700 18px/1.2 'Playfair Display',serif; }
    .guide-step p { margin:0; color:#718078; font-size:12px; line-height:1.65; }
    .guide-step a { color:#b75032; font-weight:700; text-decoration:underline; text-underline-offset:3px; }
    .guide-note { display:flex; gap:12px; align-items:flex-start; margin-top:15px; padding:15px 17px; color:#52645a; background:#edf3e9; font-size:12px; line-height:1.6; }
    .guide-note strong { color:#18352d; }
    .guide-note-mark { color:#d96b44; font-size:18px; line-height:1; }
    .guide-faq { display:grid; gap:9px; }
    .guide-faq details { padding:15px 18px; background:#fffefa; border:1px solid #e3e7df; }
    .guide-faq summary { color:#18352d; font-size:13px; font-weight:700; cursor:pointer; }
    .guide-faq p { margin:10px 0 0; color:#718078; font-size:12px; line-height:1.65; }
    .guide-footer { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px; margin-top:34px; padding:22px; background:#f0eee5; }
    .guide-footer p { margin:0; color:#52645a; font-size:13px; }
    .guide-footer .button { flex:none; }
    @media(max-width:760px) { .guide-page { padding:28px 0 54px; }.guide-hero { padding:32px 24px; }.guide-hero::after { right:-110px; }.guide-section-heading { align-items:flex-start; flex-direction:column; }.guide-section-heading h2 { font-size:25px; }.guide-steps { grid-template-columns:1fr; gap:10px; }.guide-step { min-height:0; padding:18px; }.guide-step-number { margin-bottom:12px; }.guide-footer { align-items:stretch; flex-direction:column; }.guide-footer .button { justify-content:space-between; } }
</style>

<div class="guide-page">
    <section class="guide-hero">
        <p class="eyebrow">PANDUAN LEHBOOK</p>
        <h1>Semua langkah,<br><em>jadi lebih mudah.</em></h1>
        <p>Panduan singkat untuk mencari buku, meminjam, dan mengelola koleksi perpustakaan. Pilih bagian yang kamu perlukan.</p>
    </section>

    <nav class="guide-contents" aria-label="Daftar isi panduan">
        <a href="#anggota">Untuk anggota</a>
        <a href="#pengembalian">Pengembalian & denda</a>
        <a href="#bantuan">Pertanyaan umum</a>
    </nav>

    <section class="guide-section" id="anggota">
        <div class="guide-section-heading">
            <div><p class="eyebrow">MULAI MEMBACA</p><h2>Untuk anggota</h2></div>
            <span>3 langkah mudah</span>
        </div>
        <div class="guide-steps">
            <article class="guide-step"><span class="guide-step-number">01</span><h3>Temukan buku</h3><p>Buka <a href="{{ route('catalog') }}">katalog</a>, lalu cari judul, penulis, atau ISBN. Gunakan filter kategori dan ketersediaan untuk mempersempit pilihan.</p></article>
            <article class="guide-step"><span class="guide-step-number">02</span><h3>Buat reservasi</h3><p>Pilih <strong>Pinjam</strong>, setujui ketentuan, lalu konfirmasi. QR peminjaman akan muncul di halaman <a href="{{ Auth::check() ? route('loans.mine') : route('login') }}">Peminjaman</a>.</p></article>
            <article class="guide-step"><span class="guide-step-number">03</span><h3>Ambil buku</h3><p>Tunjukkan QR kepada admin saat mengambil buku. Masa pinjam 7 hari dimulai setelah admin mengonfirmasi serah-terima.</p></article>
        </div>
        <div class="guide-note"><span class="guide-note-mark" aria-hidden="true">✦</span><span><strong>Belum bisa meminjam?</strong> Pastikan sudah masuk ke akun aktif. Jika akun sekolah masih menunggu persetujuan, hubungi petugas perpustakaan.</span></div>
    </section>

    <section class="guide-section" id="pengembalian">
        <div class="guide-section-heading">
            <div><p class="eyebrow">SEBELUM SELESAI</p><h2>Pengembalian & denda</h2></div>
            <span>Informasi penting</span>
        </div>
        <div class="guide-steps">
            <article class="guide-step"><span class="guide-step-number">01</span><h3>Kembalikan ke petugas</h3><p>Bawa buku ke perpustakaan dan serahkan kepada admin agar kondisi buku dicatat dengan benar.</p></article>
            <article class="guide-step"><span class="guide-step-number">02</span><h3>Admin mencatat kondisi</h3><p>Petugas memilih kondisi buku saat memproses pengembalian. Denda keterlambatan dihitung Rp2.000 per hari.</p></article>
            <article class="guide-step"><span class="guide-step-number">03</span><h3>Cek status dan denda</h3><p>Lihat status terbaru dan rincian denda pada halaman <a href="{{ Auth::check() ? route('loans.mine') : route('login') }}">Peminjaman</a>. Pembayaran denda dilakukan kepada petugas.</p></article>
        </div>
        <div class="guide-note"><span class="guide-note-mark" aria-hidden="true">!</span><span><strong>Besaran denda kondisi:</strong> rusak ringan 10%, rusak berat 50%, dan hilang 100% dari harga acuan buku. Jika buku tidak tersedia kembali untuk dipinjam, tanyakan kepada petugas.</span></div>
    </section>

    <section class="guide-section" id="bantuan">
        <div class="guide-section-heading">
            <div><p class="eyebrow">MASIH BINGUNG?</p><h2>Pertanyaan umum</h2></div>
        </div>
        <div class="guide-faq">
            <details><summary>QR tidak terbaca, harus bagaimana?</summary><p>Naikkan kecerahan layar dan tampilkan QR dengan ukuran penuh. Jika masih sulit dipindai, tunjukkan kode 5 digit di bawah QR kepada petugas perpustakaan.</p></details>
            <details><summary>Kapan masa pinjam dimulai?</summary><p>Masa pinjam 7 hari dimulai saat admin berhasil mengonfirmasi serah-terima, bukan saat reservasi dibuat.</p></details>
            <details><summary>Di mana saya bisa melihat status peminjaman?</summary><p>Buka menu Peminjaman setelah masuk. Di sana tersedia status, tanggal jatuh tempo, dan rincian denda jika ada.</p></details>
        </div>
    </section>

    <div class="guide-footer">
        <p>Siap mencari bacaan berikutnya?</p>
        <a class="button button-primary" href="{{ route('catalog') }}">Jelajahi katalog <span>↗</span></a>
    </div>
</div>
@endsection
