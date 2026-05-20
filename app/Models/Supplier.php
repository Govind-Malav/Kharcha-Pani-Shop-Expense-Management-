<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function credits()
    {
        return $this->hasMany(Credit::class, 'supplier_id')->where('type', 'supplier');
    }
}
