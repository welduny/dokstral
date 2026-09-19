<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    protected $fillable = ['user_id', 'book_id', 'tanggal_pinjam', 'tanggal_kembali', 'status', 'denda'];
    protected $casts = ['tanggal_pinjam' => 'date', 'tanggal_kembali' => 'date', 'denda' => 'integer'];

    public function user() { return $this->belongsTo(User::class); }
    public function book() { return $this->belongsTo(Book::class); }
}