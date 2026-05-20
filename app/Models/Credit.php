<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    protected $table = 'credits';

    protected $fillable = [
        'type',
        'customer_id',
        'supplier_id',
        'person_name',
        'phone',
        'amount',
        'paid_amount',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function payments()
    {
        return $this->hasMany(CreditPayment::class);
    }

    public function getRemainingBalanceAttribute()
    {
        return max(0, $this->amount - $this->paid_amount);
    }
}
