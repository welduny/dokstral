<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nama',
        'nis',
        'kelas',
        'email',
        'password',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function bookFavorites()
    {
        return $this->hasMany(BookFavorite::class);
    }

    public function bookReviews()
    {
        return $this->hasMany(BookReview::class);
    }

    public function readingChallenges()
    {
        return $this->hasMany(ReadingChallenge::class);
    }

    public function adminPasswordResetCode()
    {
        return $this->hasOne(AdminPasswordResetCode::class);
    }

    public function hasUnpaidFine(): bool
    {
        return Fine::whereHas('loan', fn ($query) => $query->where('user_id', $this->id))
            ->where('status_bayar', 'unpaid')
            ->exists();
    }
}
