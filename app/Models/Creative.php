<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Parental\HasParent;

class Creative extends User
{
    use HasFactory, HasParent;


    public function scopeByMinRate($query, $value)
    {
        return $query->whereHas('pricing', function ($q) use ($value) {
            $q->where('minimum_charge', '>=', $value);
        });
    }

    public function creative_categories()
    {
        return $this->belongsToMany(CreativeCategory::class, 'creative_category_creative', 'creative_id', 'creative_category_id');
    }


}
