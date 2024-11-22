<?php

namespace App\Filters;

use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class PhotoInsensitiveLikeFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        $value = strtolower($value);

        return $query
            ->select('photos.*')
            ->addSelect(DB::raw("
                (CASE
                    WHEN LOWER(photos.title) LIKE ? THEN 5
                    WHEN LOWER(photos.description) LIKE ? THEN 4
                    WHEN LOWER(photos.slug) LIKE ? THEN 3
                    WHEN EXISTS (
                        SELECT 1 FROM photo_tag
                        INNER JOIN photo_tags ON photo_tags.id = photo_tag.photo_tag_id
                        WHERE photo_tag.photo_id = photos.id
                        AND LOWER(photo_tags.name) LIKE ?
                    ) THEN 2
                    WHEN EXISTS (
                        SELECT 1 FROM photo_category_photo
                        INNER JOIN photo_categories ON photo_categories.id = photo_category_photo.photo_category_id
                        WHERE photo_category_photo.photo_id = photos.id
                        AND LOWER(photo_categories.photo_category) LIKE ?
                    ) THEN 1
                    ELSE 0
                END) AS relevance
            "))
            ->setBindings([
                "{$value}%",   // For title
                "%{$value}%",  // For description
                "%{$value}%",  // For slug
                "%{$value}%",  // For tags
                "%{$value}%",  // For categories
            ], 'select')
            ->where(function ($query) use ($value) {
                $query->whereRaw('LOWER(photos.title) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(photos.description) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(photos.slug) LIKE ?', ["%{$value}%"])
                    ->orWhereHas('tags', function ($tagQuery) use ($value) {
                        $tagQuery->whereRaw('LOWER(name) LIKE ?', ["%{$value}%"]);
                    })
                    ->orWhereHas('photo_categories', function ($categoryQuery) use ($value) {
                        $categoryQuery->whereRaw('LOWER(photo_category) LIKE ?', ["%{$value}%"]);
                    });
            })
            ->orderByDesc('relevance');
    }
}
