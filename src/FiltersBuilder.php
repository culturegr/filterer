<?php

namespace CultureGr\Filterer;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class FiltersBuilder
{
    public function __construct(
        protected Builder $builder,
        protected array $customFilters = []
    ){}

    public function apply(array $filters): Builder
    {
        foreach ($filters as $filter) {
            $filter['match'] = $filters['filter_match'] ?? 'and';
            $this->applyFilterToBuilder($filter);
        }

        return $this->builder;
    }

    private function applyFilterToBuilder(array $filter): void
    {
        if ($this->isInvalidFilter($filter)) {
            return;
        }

        if ($this->isCustomFilter($filter)) {
            $this->applyCustomFilter($filter);
        } else {
            $this->applyStandardFilter($filter);
        }
    }

    private function isInvalidFilter(array $filter): bool
    {
        return empty($filter['column']) || empty($filter['operator']);
    }

    private function isCustomFilter(array $filter): bool
    {
        return isset($this->customFilters[$filter['column']]);
    }

    private function applyCustomFilter(array $filter): void
    {
        $customFilterClass = $this->customFilters[$filter['column']];
        if (!class_exists($customFilterClass)) {
            throw new \RuntimeException("Custom filter class '{$customFilterClass}' does not exist.");
        }
        (new $customFilterClass)->apply($this->builder, $filter);
    }

    private function applyStandardFilter(array $filter): void
    {
        if ($this->isRelationalFilter($filter['column'])) {
            $this->applyRelationalFilter($filter);
        } else {
            $this->applyDirectFilter($filter);
        }
    }

    private function isRelationalFilter(string $column): bool
    {
        return str_contains($column, '.');
    }

    private function applyRelationalFilter(array $filter): void
    {
        [$relation, $column] = explode('.', $filter['column'], 2);
        $filter['column'] = $column;

        $this->builder->whereHas($relation, function ($query) use ($filter) {
            $this->applyOperator($filter, $query);
        });
    }

    private function applyDirectFilter(array $filter): void
    {
        $this->applyOperator($filter, $this->builder);
    }

    private function applyOperator(array $filter, $query): void
    {
        $operator = Str::camel($filter['operator']);
        if (!method_exists($this, $operator)) {
            throw new \InvalidArgumentException("Unsupported filter operator: {$filter['operator']}");
        }
        $this->$operator($filter, $query);
    }

    private function equalTo(array $filter, Builder $query): Builder
    {
        return $query->where($filter['column'], '=', $filter['query_1'], $filter['match']);
    }

    private function notEqualTo(array $filter, Builder $query): Builder
    {
        return $query->where($filter['column'], '<>', $filter['query_1'], $filter['match']);
    }

    private function lessThan(array $filter, Builder $query): Builder
    {
        return $query->where($filter['column'], '<', $filter['query_1'], $filter['match']);
    }

    private function greaterThan(array $filter, Builder $query): Builder
    {
        return $query->where($filter['column'], '>', $filter['query_1'], $filter['match']);
    }

    private function between(array $filter, Builder $query): Builder
    {
        return $query->whereBetween($filter['column'], [
            $filter['query_1'],
            $filter['query_2'],
        ], $filter['match']);
    }

    private function notBetween(array $filter, Builder $query): Builder
    {
        return $query->whereNotBetween($filter['column'], [
            $filter['query_1'],
            $filter['query_2'],
        ], $filter['match']);
    }

    private function contains(array $filter, Builder $query): Builder
    {
        return $query->where($filter['column'], 'like', '%' . $filter['query_1'] . '%', $filter['match']);
    }

    private function startsWith(array $filter, Builder $query): Builder
    {
        return $query->where($filter['column'], 'like', $filter['query_1'] . '%', $filter['match']);
    }

    private function betweenDate(array $filter, Builder $query): Builder
    {
        return $query->whereBetween($filter['column'], [
            Carbon::parse($filter['query_1']),
            Carbon::parse($filter['query_2']),
        ], $filter['match']);
    }

    private function in(array $filter, Builder $query): Builder
    {
        return $query->whereIn($filter['column'], $filter['query_1'], $filter['match']);
    }
}
