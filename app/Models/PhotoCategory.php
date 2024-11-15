<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PhotoCategory extends Model
{
    use HasFactory;

    protected $fillable = ['image_public_id', 'image_url', 'photo_category'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            Log::info('Model before saving:', $model->toArray());
        });
    }


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
