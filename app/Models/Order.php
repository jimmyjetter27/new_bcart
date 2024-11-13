<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Order extends Model
{

    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_number',
        'total_price',
        'transaction_status'
    ];

//    public function orderables()
//    {
//        return $this->morphToMany(Photo::class, 'orderable');
//    }


    public function photos()
    {
        return $this->morphedByMany(Photo::class, 'orderable');
    }


    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
