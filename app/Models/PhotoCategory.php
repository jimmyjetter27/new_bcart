<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoCategory extends Model
{
    use HasFactory;

    protected $fillable = ['photo_category', 'image'];

    public function photos()
    {
        return $this->belongsToMany(
            Photo::class,
            'photo_category_photo',
            'photo_category_id',
            'photo_id'
        );
    }
}
