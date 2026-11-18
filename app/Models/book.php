<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    public const CATEGORIES = [
        'Fantasi',
        'Fiksi',
        'Filsafat',
        'Keuangan',
        'Pengembangan Diri',
        'Teknologi',
    ];

    protected $fillable = ['judul', 'penulis', 'isbn', 'kategori', 'harga', 'lokasi_rak', 'deskripsi', 'cover_url', 'cover_path', 'is_circulating', 'status', 'jumlah_total', 'stok_tersedia'];

    protected $casts = ['is_circulating' => 'boolean', 'harga' => 'integer', 'jumlah_total' => 'integer', 'stok_tersedia' => 'integer'];

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function favorites()
    {
        return $this->hasMany(BookFavorite::class);
    }

    public function reviews()
    {
        return $this->hasMany(BookReview::class);
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) ($this->reviews_avg_rating ?? $this->reviews()->avg('rating') ?? 0), 1);
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_path
            ? asset('storage/'.ltrim($this->cover_path, '/'))
            : $this->cover_url;
    }

    public function syncAvailabilityStatus(): void
    {
        $this->update([
            'status' => $this->is_circulating && $this->stok_tersedia > 0
                ? 'tersedia'
                : 'tidak_tersedia',
        ]);
    }
}
