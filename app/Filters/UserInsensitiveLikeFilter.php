<?php

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class UserInsensitiveLikeFilter implements Filter
{
    protected $value;

    public function __construct($value = null)
    {
        $this->value = strtolower($value);
    }

    public function __invoke(Builder $query, $value, string $property): Builder
    {
        $value = $this->value;

        return $query->where(function ($query) use ($value) {
            $query->whereRaw('LOWER(first_name) LIKE ?', ["%{$value}%"])
                ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$value}%"])
                ->orWhereRaw('LOWER(username) LIKE ?', ["%{$value}%"])
                ->orWhereRaw('LOWER(email) LIKE ?', ["%{$value}%"])
                ->orWhereRaw('LOWER(phone_number) LIKE ?', ["%{$value}%"])
                ->orWhereRaw('LOWER(ghana_post_gps) LIKE ?', ["%{$value}%"])
                ->orWhereRaw('LOWER(city) LIKE ?', ["%{$value}%"])
                ->orWhereHas('creative_categories', function ($q) use ($value) {
                    $q->whereRaw('LOWER(creative_category) LIKE ?', ["%{$value}%"]);
                })
                ->orWhereHas('photos.photo_categories', function ($q) use ($value) {
                    $q->whereRaw('LOWER(photo_category) LIKE ?', ["%{$value}%"]);
                });
        });
    }
}

