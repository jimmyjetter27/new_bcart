<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pricing extends Model
{

    use HasFactory;

    protected $fillable = [
        'creative_id',
        'pricing_type',
        'hourly_rate',
        'daily_rate',
        'minimum_charge',
        'one_day_traditional',
        'one_day_white',
        'one_day_white_traditional',
        'two_days_white_traditional',
        'three_days_thanksgiving',
        'other_charges',
    ];

    public function creative()
    {
        return $this->belongsTo(User::class, 'creative_id');
    }

}
