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
        // Buat Admin
        User::create([
            'name' => 'Admin Perpus',
            'nama' => 'Admin Perpus',
            'email' => 'admin@lehbook.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Buat Siswa
        User::create([
            'name' => 'Rizky Siswa',
            'nama' => 'Rizky Siswa',
            'email' => 'siswa@lehbook.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'status' => 'active',
        ]);

        // Data Buku Contoh
        Book::create(['judul' => 'Laravel untuk Pemula', 'penulis' => 'Taylor Otwell', 'isbn' => '978000000001', 'kategori' => 'Teknologi', 'jumlah_total' => 5, 'stok_tersedia' => 5]);
        Book::create(['judul' => 'Koding itu Mudah', 'penulis' => 'Dicoding Team', 'isbn' => '978000000002', 'kategori' => 'Teknologi', 'jumlah_total' => 3, 'stok_tersedia' => 3]);
    }
}
