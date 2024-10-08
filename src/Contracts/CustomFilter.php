<?php

namespace CultureGr\Filterer\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface CustomFilter
{
    /**
     * Apply the custom filter to the query builder.
     *
     * @param Builder $builder The query builder instance
     * @param array<string, mixed> $filter The filter array
     *
     * @phpstan-param array{
     *      column: string,
     *      operator: string,
     *      query_1: string,
     *      query_2: string
     *  } $filter
     */
    public function apply(Builder $builder, array $filter): void;
}
