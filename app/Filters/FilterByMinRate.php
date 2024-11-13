<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Support\Facades\DB;

class FilterByMinRate implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        return $query->whereHas('pricing', function ($q) use ($value) {
            switch ($value) {
                case 'below_1000':
                    $q->where('minimum_charge', '<', 1000);
                    break;

                case '1000_to_2500':
                    $q->whereBetween('minimum_charge', [1000, 2500]);
                    break;

                case '2500_to_5000':
                    $q->whereBetween('minimum_charge', [2500, 5000]);
                    break;

                case 'above_5000':
                    $q->where('minimum_charge', '>', 5000);
                    break;

                default:
                    if (is_numeric($value)) {
                        $q->where('minimum_charge', '>=', $value);
                    }
            }
        });
    }
}
