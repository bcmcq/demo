<?php

namespace App\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class HasRelationFilter implements Filter
{
    /**
     * @param  mixed  $value
     */
    public function __invoke(Builder $query, $value, string $property): void
    {
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);

        if ($value) {
            $query->whereHas($property);
        } else {
            $query->whereDoesntHave($property);
        }
    }
}
