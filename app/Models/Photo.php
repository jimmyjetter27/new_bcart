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

    public function getPriceAttribute($value)
    {
        return $value ?? 0;
    }

    public function isStoredInCloudinary()
    {
        // Cloudinary public IDs typically do not contain file extensions like '.jpg' or '.png'
        return !str_contains($this->image_public_id, '.');
    }

    public function orders()
    {
        return $this->morphToMany(Order::class, 'orderable', 'orderables');
    }


    public function hasPurchasedPhoto($userId)
    {
        // orders table to track this
        return $this->whereHas('orders', function ($query) use ($userId) {
            $query->where('customer_id', $userId)
                ->where('transaction_status', 'completed');
        })->exists();
    }

    public function freeImage()
    {
        return $this->price == 0 || is_null($this->price);
    }

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


}
