<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReadingChallenge extends Model
{
    protected $fillable = ['user_id', 'year', 'month', 'target'];
    protected $casts = ['year' => 'integer', 'month' => 'integer', 'target' => 'integer'];

    public function user() { return $this->belongsTo(User::class); }
}