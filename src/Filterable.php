<?php

namespace CultureGr\Filterer;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

trait Filterable
{
    /**
     * @param $query
     * @param  array  $queryString The parsed query string
     * @return mixed
     * @throws ValidationException
     */
    public function scopeFilter($query, array $queryString)
    {
        $this->validateQueryString($queryString);

        return $query
            ->when(isset($queryString['filters']), fn ($q) => $this->applyFiltersToBuilder($q, $queryString['filters']))
            ->when(isset($queryString['sorts']), fn ($q) => $this->applySortsToBuilder($q, $queryString['sorts']))
            ->when(isset($queryString['limit']), fn ($q) => $q->paginate($queryString['limit']));
    }

    protected function validateQueryString(array $queryString): void
    {
        $validator = validator()->make($queryString, [
            //'filter_match' => 'sometimes|required|in:and,or',
            'filters' => 'sometimes|required|array',
            'filters.*.column' => 'required_with:f|in:'.$this->allowedFilterable(),
            'filters.*.operator' => 'required_with:f.*.column|in:'.$this->allowedOperators(),
            'filters.*.query_1' => 'required_with:f.*.column',
            'filters.*.query_2' => 'required_if:f.*.operator,between,not_between',
            'sorts' => 'sometimes|required|array',
            'sorts.*.column' => 'required_with:f|in:'.$this->allowedSortable(),
            'sorts.*.direction' => 'required_with:f.*.column',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    protected function applyFiltersToBuilder(Builder $builder, array $filters): Builder
    {
        return (new FiltersBuilder($builder))->apply($filters);
    }

    protected function applySortsToBuilder(Builder $builder, array $orders): Builder
    {
        return (new SortsBuilder($builder))->apply($orders);
    }

    protected function allowedFilterable(): string
    {
        return implode(',', $this->filterable);
    }

    protected function allowedSortable(): string
    {
        return implode(',', $this->sortable);
    }

    protected function allowedOperators(): string
    {
        return implode(',', [
            'equal_to',
            'not_equal_to',
            'less_than',
            'greater_than',
            'between',
            'not_between',
            'contains',
            'starts_with',
            'between_date',
            'in',
        ]);
    }
}
