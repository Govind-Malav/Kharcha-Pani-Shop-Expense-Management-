<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $table = 'sales';

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'customer_name',
        'total_amount',
        'tax_amount',
        'payment_method',
        'date',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
