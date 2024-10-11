<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class FiltersByFullName implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        // Split the full name by space, assuming the full name is provided in the format "first_name last_name"
        $names = explode(' ', $value);

        // Check if we have at least two parts (first name and last name)
        if (count($names) === 2) {
            $firstName = $names[0];
            $lastName = $names[1];

            // Filter by both first name and last name
            $query->where('first_name', 'like', "%{$firstName}%")
                ->where('last_name', 'like', "%{$lastName}%");
        } else {
            // If it's only one name, search in both first name and last name
            $query->where(function (Builder $query) use ($value) {
                $query->where('first_name', 'like', "%{$value}%")
                    ->orWhere('last_name', 'like', "%{$value}%");
            });
        }
    }
}
