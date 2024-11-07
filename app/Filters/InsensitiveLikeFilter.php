<?php

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class InsensitiveLikeFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        // Search across multiple fields
        return $query->where(function ($query) use ($value) {
            $value = strtolower($value);
            $query->whereRaw('LOWER(first_name) LIKE ?', ["%{$value}%"])
                ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$value}%"])
                ->orWhereRaw('LOWER(username) LIKE ?', ["%{$value}%"])
                ->orWhereRaw('LOWER(email) LIKE ?', ["%{$value}%"])
                ->orWhereRaw('LOWER(phone_number) LIKE ?', ["%{$value}%"])
                ->orWhereRaw('LOWER(ghana_post_gps) LIKE ?', ["%{$value}%"])
                ->orWhereRaw('LOWER(city) LIKE ?', ["%{$value}%"]);
        });
    }
}
