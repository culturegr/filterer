<?php

namespace CultureGr\Filterer;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

trait Filterable
{
    /**
     * Apply filters and sorting to the query, then paginate the results.
     * @param Builder $query The query builder instance
     * @param array $queryString An array containing filter, sort, and pagination parameters
     * @return Builder The filtered results
     * @throws ValidationException If the query string contains invalid parameters
     */
    public function scopeFilter(Builder $query, array $queryString): Builder
    {
        $this->validateQueryString($queryString);

        return $query
            ->when(isset($queryString['filters']), fn($q) => $this->applyFiltersToBuilder($q, $queryString['filters']))
            ->when(isset($queryString['sorts']), fn($q) => $this->applySortsToBuilder($q, $queryString['sorts']));
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
                fn($q) => $q->paginate($queryString['limit']),
                fn($q) => $q->paginate($defaultLimit)
            );
    }

    protected function validateQueryString(array $queryString): void
    {
        $validator = validator()->make($queryString, [
            // TODO: 'filter_match' => 'sometimes|required|in:and,or',
            'filters' => 'sometimes|required|array',
            'filters.*.column' => 'required_with:f.*.column|in:' . $this->allowedFilterables(),
            'filters.*.operator' => 'required_with:f.*.column|in:' . $this->allowedOperators(),
            'filters.*.query_1' => 'required_with:f.*.column',
            'filters.*.query_2' => 'required_if:f.*.operator,between,not_between',
            'sorts' => 'sometimes|required|array',
            'sorts.*.column' => 'required_with:f|in:' . $this->allowedSortable(),
            'sorts.*.direction' => 'required_with:f.*.column',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    protected function applyFiltersToBuilder(Builder $builder, array $filters): Builder
    {
        return (new FiltersBuilder($builder, $this->getCustomFilters()))->apply($filters);
    }

    protected function applySortsToBuilder(Builder $builder, array $orders): Builder
    {
        return (new SortsBuilder($builder))->apply($orders);
    }

    protected function allowedFilterables(): string
    {
        return implode(',', array_merge($this->getFilterables(), array_keys($this->getCustomFilters())));
    }

    protected function allowedCustomFilters(): string
    {
        return implode(',', array_keys($this->getCustomFilters()));
    }

    protected function allowedSortable(): string
    {
        return implode(',', $this->getSortables());
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

    protected function getFilterables(): array
    {
        return $this->filterable ?? [];
    }

    protected function getSortables(): array
    {
        return $this->sortable ?? [];
    }

    protected function getCustomFilters(): array
    {
        return $this->customFilters ?? [];
    }
}
