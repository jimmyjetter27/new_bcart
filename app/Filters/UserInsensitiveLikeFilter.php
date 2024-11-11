<?php

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class UserInsensitiveLikeFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        $value = strtolower($value);

        return $query
            ->selectRaw("
                users.*,
                (CASE
                    WHEN LOWER(first_name) LIKE ? THEN 3
                    WHEN LOWER(last_name) LIKE ? THEN 3
                    WHEN LOWER(username) LIKE ? THEN 2
                    WHEN LOWER(email) LIKE ? THEN 2
                    WHEN LOWER(city) LIKE ? THEN 1
                    ELSE 0
                END) AS relevance
            ", [
                "{$value}%", // prioritize matches that start with the keyword
                "{$value}%",
                "{$value}%",
                "{$value}%",
                "{$value}%"
            ])
            ->where(function ($query) use ($value) {
                $query->whereRaw('LOWER(first_name) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(username) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(phone_number) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(ghana_post_gps) LIKE ?', ["%{$value}%"])
                    ->orWhereRaw('LOWER(city) LIKE ?', ["%{$value}%"]);
            })
            ->orderByDesc('relevance'); // Order by relevance score
    }
}
