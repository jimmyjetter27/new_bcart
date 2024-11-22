<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Order extends Model
{

    use HasFactory;

    protected $fillable = [
        'customer_id',
        'guest_identifier',
        'order_number',
        'total_price',
        'transaction_status'
    ];

//    public function orderables()
//    {
//        return $this->morphToMany(Photo::class, 'orderable');
//    }

//    public function orderables(): MorphToMany
//    {
//        return $this->morphToMany(Photo::class, 'orderable', 'orderables');
//    }

    public function orderables()
    {
        return $this->hasMany(Orderable::class, 'order_id');
    }


//    public function photos()
//    {
//        return $this->morphedByMany(Photo::class, 'orderable', 'orderables');
//    }

    public function photos()
    {
        return $this->morphedByMany(Photo::class, 'orderable', 'orderables', 'order_id', 'orderable_id');
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
