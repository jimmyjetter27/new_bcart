<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hiring extends Model
{

    use HasFactory;

    protected $fillable = [
        'creative_id',
        'regular_user_id',
        'hire_date',
        'location',
        'num_days',
        'num_hours',
        'description'
    ];

    public function creative()
    {
        return $this->belongsTo(Creative::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'regular_user_id');
    }

//    public function payment()
//    {
//        return $this->morphOne(Payment::class, 'payable');
//    }

    public function categories()
    {
        return $this->belongsToMany(CreativeCategory::class, 'hiring_creative_categories');
    }
}
