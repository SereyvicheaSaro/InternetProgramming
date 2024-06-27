<?php

namespace App\Models\Order;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'receipt_number',
        'cashier_id',
        'total_price',
    ];

    public function cashier(){
        return $this->belongsTo(User::class);
    }
    public function orderDetails()
    {
        return $this->hasMany(Detail::class);
    }
}
