<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nama')->nullable()->after('name');
            $table->string('status')->default('active')->after('role');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->string('isbn')->nullable()->unique()->after('penulis');
            $table->string('kategori')->nullable()->after('isbn');
            $table->unsignedInteger('jumlah_total')->default(0)->after('stok');
            $table->unsignedInteger('stok_tersedia')->default(0)->after('jumlah_total');
        });

        DB::table('books')->update([
            'jumlah_total' => DB::raw('stok'),
            'stok_tersedia' => DB::raw('stok'),
        ]);

        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal_pinjam');
            $table->date('tenggat_kembali');
            $table->date('tanggal_kembali')->nullable();
            $table->enum('status', ['pending', 'borrowed', 'returned', 'rejected', 'overdue'])->default('borrowed');
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['book_id', 'status']);
        });

        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('jumlah_denda');
            $table->enum('status_bayar', ['unpaid', 'paid'])->default('unpaid');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
        Schema::dropIfExists('loans');
        Schema::table('books', function (Blueprint $table) {
            $table->dropUnique(['isbn']);
            $table->dropColumn(['isbn', 'kategori', 'jumlah_total', 'stok_tersedia']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nama', 'status']);
        });
    }
};