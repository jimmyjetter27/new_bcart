<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoCategory extends Model
{
    use HasFactory;

    protected $fillable = ['image_public_id', 'image_url', 'photo_category'];

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
