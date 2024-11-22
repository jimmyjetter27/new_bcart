<?php

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class UserInsensitiveLikeFilter implements Filter
{
    protected $value;

    public function __construct($value)
    {
        $this->value = strtolower($value);
    }

    public function __invoke(Builder $query, $value, string $property): Builder
    {
        $value = $this->value;

        return $query
            ->select('users.*')
            ->leftJoin('creative_category_creative', 'users.id', '=', 'creative_category_creative.creative_id')
            ->leftJoin('creative_categories', 'creative_categories.id', '=', 'creative_category_creative.creative_category_id')
            ->leftJoin('photos', 'users.id', '=', 'photos.user_id')
            ->leftJoin('photo_category_photo', 'photos.id', '=', 'photo_category_photo.photo_id')
            ->leftJoin('photo_categories', 'photo_categories.id', '=', 'photo_category_photo.photo_category_id')
            ->where(function ($query) use ($value) {
                $query->whereRaw('LOWER(users.first_name) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(users.last_name) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(users.username) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(users.email) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(users.phone_number) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(users.ghana_post_gps) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(users.city) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(creative_categories.creative_category) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(photo_categories.photo_category) LIKE ?', ["%{$value}%"]);
            })
            ->selectRaw("
                (CASE
                    WHEN LOWER(users.first_name) LIKE ? THEN 5
                    WHEN LOWER(users.last_name) LIKE ? THEN 5
                    WHEN LOWER(users.username) LIKE ? THEN 4
                    WHEN LOWER(users.email) LIKE ? THEN 4
                    WHEN LOWER(creative_categories.creative_category) LIKE ? THEN 3
                    WHEN LOWER(photo_categories.photo_category) LIKE ? THEN 2
                    WHEN LOWER(users.city) LIKE ? THEN 1
                    ELSE 0
                END) AS relevance
            ", [
                "{$value}%",
                "{$value}%",
                "{$value}%",
                "{$value}%",
                "%{$value}%",
                "%{$value}%",
                "%{$value}%"
            ])
            ->orderByDesc('relevance')
            ->distinct();
    }
}
