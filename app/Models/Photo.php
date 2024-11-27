<?php

namespace App\Models;

use App\Scopes\ApprovedPhotoScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

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
        'photo_category_id',
        'is_banner'
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

    public function isUploader()
    {
        $user = auth('sanctum')->user();
        return $user && intval($user->id) === intval($this->user_id);
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



//    public function hasPurchasedPhoto($userId)
//    {
//        return $this->orders()
//            ->where('customer_id', $userId)
//            ->where('transaction_status', 'completed')
//            ->exists();
//    }

    // Photo.php

    public function hasPurchasedPhoto($userId = null, $guestIdentifier = null)
    {
        // If the user is the uploader, they have implicit access
//        if ($userId && $userId == $this->user_id) {
//            return true;
//        }

        Log::info('userId inside hasPurchasedPhoto: '. $userId);
        return Order::where(function ($query) use ($userId, $guestIdentifier) {
            if ($userId) {
                $query->where('customer_id', $userId);
            } elseif ($guestIdentifier) {
                $query->where('guest_identifier', $guestIdentifier);
            } else {
                $query->whereRaw('1 = 0'); // Ensures no records are returned
            }
        })
            ->where('transaction_status', 'completed')
            ->whereHas('photos', function ($query) {
                $query->where('photos.id', $this->id);
            })
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

    protected static function booted()
    {
        static::addGlobalScope(new ApprovedPhotoScope);

        static::deleting(function ($photo) {
            if ($photo->is_banner) {
                cache()->forget('banner_photo');
            }
        });
    }



}
