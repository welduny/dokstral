<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    protected $fillable = ['loan_id', 'jumlah_denda', 'status_bayar'];
    protected $casts = ['jumlah_denda' => 'integer'];

    public function loan() { return $this->belongsTo(Loan::class); }
}