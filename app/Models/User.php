<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Parental\HasChildren;
use Filament\Panel;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    use HasFactory, Notifiable, HasChildren, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'email_verified_at',
        'phone_number',
        'ghana_post_gps',
        'city',
        'physical_address',
        'password',
        'creative_hire_status',
        'creative_status',
        'profile_picture_public_id',
        'profile_picture_url',
        'hiring_id',
        'description',
        'google_id',
        'type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'creative_hire_status' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
//        return true;
        return $this->isSuperAdmin() || $this->isAdmin();
        return str_ends_with($this->email, '@yourdomain.com') && $this->hasVerifiedEmail();
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_picture_url;
    }

    public function getNameAttribute(): string
    {
        $firstName = $this->first_name ?? 'First';
        $lastName = $this->last_name ?? 'Last';
        return "{$firstName} {$lastName}";
    }

    public function isAdmin(): bool
    {
        return $this->type === Admin::class;
    }

    public function isSuperAdmin(): bool
    {
        return $this->type === SuperAdmin::class;
    }

    public function isCreative(): bool
    {
        return $this->type === Creative::class;
    }

    public function isRegularUser(): bool
    {
        return $this->type === RegularUser::class;
    }



//    public function sendEmailVerificationNotification()
//    {
//        $this->notify(new VerifyEmailNotification());
//    }


    public function pricing()
    {
        return $this->hasOne(Pricing::class, 'creative_id');
    }


    public function hiring()
    {
        return $this->hasOne(Hiring::class,
            'creative_id',
            'id',
        );
    }

    public function paymentInfo()
    {
        return $this->hasOne(PaymentInformation::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }


    public function photos()
    {
        $authenticatedUser = auth('sanctum')->user();

        if ($authenticatedUser && $authenticatedUser->id === $this->id) {
            // If the authenticated user is viewing their own profile, remove the global scope
            return $this->hasMany(Photo::class)->withoutGlobalScope('approvedFilter');
        } else {
            // For other users, apply the global scope (default behavior)
            return $this->hasMany(Photo::class);
        }
    }


    public function purchasedPhotos()
    {
        return $this->hasManyThrough(
            Photo::class,
            Order::class,
            'customer_id', // Foreign key on the orders table...
            'id',          // Foreign key on the photos table...
            'id',          // Local key on the users table...
            'orderable_id' // Local key on the orders table...
        )
            ->where('orders.transaction_status', 'completed')
            ->where('orderables.orderable_type', Photo::class);
    }


    public function creative_categories()
    {
        return $this->belongsToMany(CreativeCategory::class,
            'creative_category_creative',
            'creative_id',
            'creative_category_id'
        );
    }

    public function delete()
    {
        // Delete related data
        $this->pricing()->delete();
        $this->hiring()->delete();
        $this->paymentInfo()->delete();
        $this->orders()->delete();
        $this->photos()->delete();
        $this->creative_categories()->detach(); // For many-to-many relationships, use detach()

        // Call parent delete to delete the user record
        return parent::delete();
    }

}
