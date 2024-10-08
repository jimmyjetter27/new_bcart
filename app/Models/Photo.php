<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'title',
        'description',
        'price',
//        'image_path',
        'image_url',
        'image_public_id',
        'is_approved',
        'photo_category_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creative()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function photo_categories()
    {
        return $this->belongsToMany(
            PhotoCategory::class,
            'photo_category_photo',  // Pivot table
            'photo_id',  // Foreign key on the pivot table
            'photo_category_id'  // Related key on the pivot table
        );
    }


    public function tags()
    {
        return $this->belongsToMany(PhotoTag::class, 'photo_tag', 'photo_id', 'photo_tag_id');
    }


//    public function photo_tags()
//    {
//        return $this->belongsTo(
//            PhotoCategory::class,
//            'photo_category_id',
//            'id'
//        );
//    }

}
