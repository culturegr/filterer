<?php

namespace CultureGr\Filterer;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class FiltersBuilder
{
    protected Builder $builder;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    public function apply(array $filters): Builder
    {
        foreach ($filters as $filter) {
            $filter['match'] = $filters['filter_match'] ?? 'and';
            $this->applyFilterToBuilder($filter);
        }

        return $this->builder;
    }

    protected function applyFilterToBuilder(array $filter): void
    {
        if ('' === $filter['column'] || '' === $filter['operator']) {
            return;
        }

        if (false !== strpos($filter['column'], '.')) {
            [$relation, $filter['column']] = explode('.', $filter['column']);
            $this->builder->whereHas($relation, function ($q) use ($filter) {
                $this->{Str::camel($filter['operator'])}($filter, $q);
            });
        } else {
            $this->{Str::camel($filter['operator'])}($filter, $this->builder);
        }
    }

    protected function equalTo(array $filter, Builder $query): Builder
    {
        return $query->where($filter['column'], '=', $filter['query_1'], $filter['match']);
    }

    protected function notEqualTo(array $filter, Builder $query): Builder
    {
        return $query->where($filter['column'], '<>', $filter['query_1'], $filter['match']);
    }

    protected function lessThan(array $filter, Builder $query): Builder
    {
        return $query->where($filter['column'], '<', $filter['query_1'], $filter['match']);
    }

    protected function greaterThan(array $filter, Builder $query): Builder
    {
        return $query->where($filter['column'], '>', $filter['query_1'], $filter['match']);
    }

    protected function between(array $filter, Builder $query): Builder
    {
        return $query->whereBetween($filter['column'], [
            $filter['query_1'],
            $filter['query_2'],
        ], $filter['match']);
    }

    protected function notBetween(array $filter, Builder $query): Builder
    {
        return $query->whereNotBetween($filter['column'], [
            $filter['query_1'],
            $filter['query_2'],
        ], $filter['match']);
    }

    protected function contains(array $filter, Builder $query): Builder
    {
        return $query->where($filter['column'], 'like', '%'.$filter['query_1'].'%', $filter['match']);
    }

    protected function betweenDate(array $filter, Builder $query): Builder
    {
        return $query->whereBetween($filter['column'], [
            Carbon::parse($filter['query_1']),
            Carbon::parse($filter['query_2']),
        ], $filter['match']);
    }

    protected function in(array $filter, Builder $query): Builder
    {
        return $query->whereIn($filter['column'], $filter['query_1'], $filter['match']);
    }
}
