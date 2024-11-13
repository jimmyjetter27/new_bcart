<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orderable extends Model
{
    use HasFactory;
    protected $fillable = ['order_id', 'orderable_id', 'orderable_type'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

}
