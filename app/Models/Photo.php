<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'image_path',
        'description',
        'price',
        'approved',
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
            'photo_category_photo',
            'photo_id',
            'photo_category_id'
        );
    }

    public function photo_tags()
    {
        return $this->belongsTo(
            PhotoCategory::class,
            'photo_category_id',
            'id'
        );
    }

}
