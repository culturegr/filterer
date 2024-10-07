<?php

namespace CultureGr\Filterer;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

trait Filterable
{
    /**
     * Apply filters and sorting to the query, then paginate the results.
     *
     * @param Builder $query The query builder instance
     * @param array $queryString An array containing filter, sort, and pagination parameters
     * @return Builder The filtered results
     * @throws ValidationException If the query string contains invalid parameters
     */
    public function scopeFilter(Builder $query, array $queryString): Builder
    {
        $this->validateQueryString($queryString);

        return $query
            ->when(isset($queryString['filters']), fn ($q) => $this->applyFiltersToBuilder($q, $queryString['filters']))
            ->when(isset($queryString['sorts']), fn ($q) => $this->applySortsToBuilder($q, $queryString['sorts']));
    }

    /**
     * @param Builder $query The query builder instance
     * @param array $queryString An array containing filter, sort, and pagination parameters
     * @return LengthAwarePaginator The paginated filtered results
     * @throws ValidationException If the query string contains invalid parameters
     */
    public function scopeFilterPaginate(Builder $query, array $queryString, $defaultLimit = 10): LengthAwarePaginator
    {
        return $this->scopeFilter($query, $queryString)
            ->when(
                isset($queryString['limit']),
                fn ($q) => $q->paginate($queryString['limit']),
                fn ($q) => $q->paginate($defaultLimit)
            );
    }

    protected function validateQueryString(array $queryString): void
    {
        $validator = validator()->make($queryString, [
            // TODO: 'filter_match' => 'sometimes|required|in:and,or',
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
