<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@lehbook.com');
        $adminPassword = env('ADMIN_PASSWORD');

        if (filled($adminPassword)) {
            User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Admin Perpus',
                    'nama' => 'Admin Perpus',
                    'password' => Hash::make($adminPassword),
                    'role' => 'admin',
                    'status' => 'active',
                ]
            );
        }

        // Buat Siswa
        User::firstOrCreate(
            ['email' => 'siswa@lehbook.com'],
            [
                'name' => 'Rizky Siswa',
                'nama' => 'Rizky Siswa',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'status' => 'active',
            ]
        );

        Book::firstOrCreate(
            ['isbn' => '978000000001'],
            ['judul' => 'Laravel untuk Pemula', 'penulis' => 'Taylor Otwell', 'kategori' => 'Teknologi', 'harga' => 125000, 'lokasi_rak' => 'A-01', 'deskripsi' => 'Pengantar membangun aplikasi web modern dengan Laravel, dari routing dan database sampai fitur autentikasi.', 'cover_url' => 'https://covers.openlibrary.org/b/isbn/978000000001-L.jpg', 'jumlah_total' => 5, 'stok_tersedia' => 5]
        );
        Book::firstOrCreate(
            ['isbn' => '978000000002'],
            ['judul' => 'Koding itu Mudah', 'penulis' => 'Dicoding Team', 'kategori' => 'Teknologi', 'harga' => 95000, 'lokasi_rak' => 'A-02', 'deskripsi' => 'Langkah awal belajar pemrograman dengan latihan praktis, pola berpikir komputasional, dan proyek sederhana.', 'cover_url' => 'https://covers.openlibrary.org/b/isbn/978000000002-L.jpg', 'jumlah_total' => 3, 'stok_tersedia' => 3]
        );

        $additionalBooks = [
            [
                'isbn' => '9780735211292',
                'judul' => 'Atomic Habits',
                'penulis' => 'James Clear',
                'kategori' => 'Pengembangan Diri',
                'harga' => 110000,
                'lokasi_rak' => 'B-01',
                'deskripsi' => 'Panduan praktis membangun kebiasaan baik lewat perubahan kecil yang konsisten dan sistem yang mudah dipertahankan.',
                'cover_url' => 'https://covers.openlibrary.org/b/isbn/9780735211292-L.jpg',
                'jumlah_total' => 3,
                'stok_tersedia' => 3,
            ],
            [
                'isbn' => '9786024125189',
                'judul' => 'Filosofi Teras',
                'penulis' => 'Henry Manampiring',
                'kategori' => 'Filsafat',
                'harga' => 85000,
                'lokasi_rak' => 'C-01',
                'deskripsi' => 'Pengantar Stoisisme yang membumi untuk menghadapi emosi, tekanan, dan hal-hal yang berada di luar kendali kita.',
                'cover_url' => 'https://covers.openlibrary.org/b/isbn/9786024125189-L.jpg',
                'jumlah_total' => 4,
                'stok_tersedia' => 4,
            ],
            [
                'isbn' => '9789793062792',
                'judul' => 'Laskar Pelangi',
                'penulis' => 'Andrea Hirata',
                'kategori' => 'Fiksi',
                'harga' => 79000,
                'lokasi_rak' => 'C-02',
                'deskripsi' => 'Kisah persahabatan dan perjuangan sekelompok anak Belitung yang menjaga mimpi mereka melalui pendidikan.',
                'cover_url' => 'https://covers.openlibrary.org/b/isbn/9789793062792-L.jpg',
                'jumlah_total' => 3,
                'stok_tersedia' => 3,
            ],
            [
                'isbn' => '9786020332956',
                'judul' => 'Bumi',
                'penulis' => 'Tere Liye',
                'kategori' => 'Fantasi',
                'harga' => 92000,
                'lokasi_rak' => 'D-01',
                'deskripsi' => 'Petualangan Raib, Seli, dan Ali membuka pintu menuju dunia paralel dengan rahasia dan kekuatan yang tak terduga.',
                'cover_url' => 'https://covers.openlibrary.org/b/isbn/9786020332956-L.jpg',
                'jumlah_total' => 3,
                'stok_tersedia' => 3,
            ],
            [
                'isbn' => '9780857197689',
                'judul' => 'The Psychology of Money',
                'penulis' => 'Morgan Housel',
                'kategori' => 'Keuangan',
                'harga' => 105000,
                'lokasi_rak' => 'B-02',
                'deskripsi' => 'Cerita-cerita pendek tentang cara manusia berpikir, mengambil keputusan, dan membangun hubungan dengan uang.',
                'cover_url' => 'https://covers.openlibrary.org/b/isbn/9780857197689-L.jpg',
                'jumlah_total' => 2,
                'stok_tersedia' => 2,
            ],
        ];

        foreach ($additionalBooks as $book) {
            Book::firstOrCreate(['isbn' => $book['isbn']], $book);
        }
    }
}
