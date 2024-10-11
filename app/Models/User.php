<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Parental\HasChildren;
//use App\Notifications\VerifyEmail as VerifyEmailNotification;

class User extends Authenticatable implements MustVerifyEmail
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
        'profile_picture_public_id ',
        'profile_picture_url ',
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
        return $this->hasMany(Order::class, 'customer_id', 'id');
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function creative_categories()
    {
        return $this->belongsToMany(CreativeCategory::class,
            'creative_category_creative',
            'creative_id',
            'creative_category_id'
        );
    }
}
