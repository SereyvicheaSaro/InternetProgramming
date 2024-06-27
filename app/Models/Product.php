<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'code',
        'unit_price',
        'quantity',
        'image',
        'promotion',
        'alert',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'promotion' => 'integer',
        'alert' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Example of an accessor
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->unit_price, 2);
    }

    // Example of a mutator
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords($value);
    }

    // Example of a query scope
    public function scopeActive($query)
    {
        return $query->where('quantity', '>', 0);
    }
}
