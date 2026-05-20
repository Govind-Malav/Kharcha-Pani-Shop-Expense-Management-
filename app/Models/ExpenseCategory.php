<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExpenseCategory extends Model
{
    protected $table = 'expense_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_custom',
    ];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($category) {
            $category->slug = Str::slug($category->name);
        });
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }
}
