<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('status')->default('tersedia')->after('is_circulating');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->string('keterangan_kondisi')->nullable()->after('tanggal_kembali');
            $table->text('catatan_admin')->nullable()->after('keterangan_kondisi');
            $table->unsignedInteger('denda_terlambat')->default(0)->after('catatan_admin');
            $table->unsignedInteger('denda_tambahan')->default(0)->after('denda_terlambat');
            $table->unsignedInteger('total_denda')->default(0)->after('denda_tambahan');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['keterangan_kondisi', 'catatan_admin', 'denda_terlambat', 'denda_tambahan', 'total_denda']);
        });
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};