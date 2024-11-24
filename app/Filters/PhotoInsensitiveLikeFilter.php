<?php

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class PhotoInsensitiveLikeFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        $value = strtolower($value);

        return $query->where(function ($query) use ($value) {
            $query->whereRaw('LOWER(photos.title) LIKE ?', ["%{$value}%"])
                ->orWhereRaw('LOWER(photos.description) LIKE ?', ["%{$value}%"])
                ->orWhereRaw('LOWER(photos.slug) LIKE ?', ["%{$value}%"])
                ->orWhereHas('tags', function ($tagQuery) use ($value) {
                    $tagQuery->whereRaw('LOWER(photo_tags.name) LIKE ?', ["%{$value}%"]);
                })
                ->orWhereHas('photo_categories', function ($categoryQuery) use ($value) {
                    $categoryQuery->whereRaw('LOWER(photo_categories.photo_category) LIKE ?', ["%{$value}%"]);
                });
        });
    }
}
