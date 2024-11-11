<?php

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class PhotoInsensitiveLikeFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        $value = strtolower($value);

        return $query
            ->selectRaw("
                photos.*,
                (CASE
                    WHEN LOWER(title) LIKE ? THEN 3
                    WHEN LOWER(description) LIKE ? THEN 2
                    WHEN LOWER(slug) LIKE ? THEN 2
                    WHEN LOWER(price) LIKE ? THEN 1
                    ELSE 0
                END) AS relevance
            ", [
                "{$value}%",  // prioritize matches that start with the keyword in title
                "%{$value}%", // matches in description
                "%{$value}%", // matches in slug
                "%{$value}%", // matches in price
            ])
            ->where(function ($query) use ($value) {
                $query->whereRaw('LOWER(title) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(slug) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(price) LIKE ?', ["%{$value}%"]);

                // Search in tags
                $query->orWhereHas('tags', function ($tagQuery) use ($value) {
                    $tagQuery->whereRaw('LOWER(name) LIKE ?', ["%{$value}%"]);
                });

                // Search in categories
                $query->orWhereHas('photo_categories', function ($categoryQuery) use ($value) {
                    $categoryQuery->whereRaw('LOWER(photo_category) LIKE ?', ["%{$value}%"]);
                });
            })
            ->orderByDesc('relevance'); // Order by relevance score
    }
}
