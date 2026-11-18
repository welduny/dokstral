<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    protected $fillable = ['loan_id', 'jumlah_denda', 'status_bayar', 'paid_at'];
    protected $casts = ['jumlah_denda' => 'integer', 'paid_at' => 'datetime'];

    public function loan() { return $this->belongsTo(Loan::class); }
}