<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSetting extends Model
{
    protected $table = 'shop_settings';

    protected $fillable = [
        'shop_name',
        'shop_email',
        'shop_phone',
        'shop_address',
        'logo_path',
        'gst_number',
        'currency',
        'low_stock_threshold',
    ];
}
