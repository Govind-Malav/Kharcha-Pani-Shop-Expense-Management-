<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'name',
        'sku',
        'quantity',
        'cost_price',
        'selling_price',
        'supplier_id',
        'image_path',
        'barcode',
        'low_stock_threshold',
        'description',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'low_stock_threshold' => 'integer',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function isLowStock(): bool
    {
        // Get shop threshold fallback if not defined on product
        $threshold = $this->low_stock_threshold;
        if (is_null($threshold)) {
            $settings = ShopSetting::first();
            $threshold = $settings ? $settings->low_stock_threshold : 10;
        }
        return $this->quantity <= $threshold;
    }
}
