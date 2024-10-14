<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Support\Facades\DB;

class FilterByMinRate implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        // Map the options to their corresponding conditions
        switch ($value) {
            case 'below_1000':
                return $query->where('minimum_charge', '<', 1000);

            case '1000_to_2500':
                return $query->whereBetween('minimum_charge', [1000, 2500]);

            case '2500_to_5000':
                return $query->whereBetween('minimum_charge', [2500, 5000]);

            case 'above_5000':
                return $query->where('minimum_charge', '>', 5000);

            default:
                // For custom rate or "Rate", expect a specific value and apply the condition
                if (is_numeric($value)) {
                    return $query->where('minimum_charge', '>=', $value);
                }
                return $query;
        }
    }
}
