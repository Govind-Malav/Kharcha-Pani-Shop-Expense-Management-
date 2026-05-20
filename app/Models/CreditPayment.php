<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPayment extends Model
{
    protected $table = 'credit_payments';

    protected $fillable = [
        'credit_id',
        'amount_paid',
        'payment_date',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount_paid' => 'decimal:2',
    ];

    public function credit()
    {
        return $this->belongsTo(Credit::class);
    }
}
