<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = ['user_id', 'book_id', 'tanggal_pinjam', 'tenggat_kembali', 'tanggal_kembali', 'confirmation_token', 'confirmed_at', 'harga_buku', 'status', 'keterangan_kondisi', 'catatan_admin', 'denda_terlambat', 'denda_tambahan', 'total_denda'];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tenggat_kembali' => 'date',
        'tanggal_kembali' => 'date',
        'confirmed_at' => 'datetime',
        'harga_buku' => 'integer',
        'denda_terlambat' => 'integer',
        'denda_tambahan' => 'integer',
        'total_denda' => 'integer',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function book() { return $this->belongsTo(Book::class); }
    public function fine() { return $this->hasOne(Fine::class); }
}