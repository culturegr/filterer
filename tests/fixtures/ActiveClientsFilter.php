<?php

namespace CultureGr\Filterer\Tests\Fixtures;

use CultureGr\Filterer\Contracts\CustomFilter;
use Illuminate\Database\Eloquent\Builder;

class ActiveClientsFilter implements CustomFilter
{
    public function apply(Builder $builder, array $filter): void
    {
        if ($filter['query_1'] === true) {
            $builder->whereHas('orders', function($q) {
                $q->where('shipped_at', '>=', '2019-06-15 09:30:00');
            });
        } else {
            $builder->where(function($query) {
                $query->whereDoesntHave('orders')
                    ->orWhereHas('orders', function($q) {
                        $q->where('shipped_at', '<', '2019-06-15 09:30:00');
                    });
            });
        }
    }
}