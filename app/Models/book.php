<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['judul', 'penulis', 'isbn', 'kategori', 'is_circulating', 'status', 'jumlah_total', 'stok_tersedia'];
    protected $casts = ['is_circulating' => 'boolean', 'jumlah_total' => 'integer', 'stok_tersedia' => 'integer'];

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}