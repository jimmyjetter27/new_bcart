<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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
        'image_width',
        'image_height',
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

//    public function orders()
//    {
//        return $this->morphedByMany(Order::class, 'orderable', 'orderables', 'orderable_id', 'order_id');
//    }

    public function orders()
    {
        return $this->morphToMany(Order::class, 'orderable', 'orderables', 'orderable_id', 'order_id');
    }


//    public function orders()
//    {
//        return $this->morphedByMany(Order::class, 'orderable', 'orderables', 'orderable_id', 'order_id')
//            ->where('orderables.orderable_type', '=', self::class);
//    }



    public function hasPurchasedPhoto($userId)
    {
        return $this->orders()
            ->where('customer_id', $userId)
            ->where('transaction_status', 'completed')
            ->exists();
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

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('approvedFilter', function (Builder $builder) {
            $user = auth('sanctum')->user();

            // Apply the filter only if the user is not an admin, super admin, or the creative who uploaded the image
            if (!$user || (!$user->isAdmin() && !$user->isSuperAdmin())) {
                $builder->where(function ($query) use ($user) {
                    $query->where('is_approved', true)
                        ->orWhere('user_id', $user?->id); // Allow the creative to see their own unapproved images
                });
            }
        });
    }

}
