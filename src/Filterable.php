<?php

namespace CultureGr\Filterer;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
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
     * @param int|null $defaultLimit The default number of items per page (null to use config)
     * @return LengthAwarePaginator The paginated filtered results
     * @throws ValidationException If the query string contains invalid parameters
     */
    public function scopeFilterPaginate(Builder $query, array $queryString, $defaultLimit = null): LengthAwarePaginator
    {
        return $this->paginateFiltered($query, $queryString, $defaultLimit, 'paginate');
    }

    /**
     * Apply filters and sorting to the query, then simple paginate the results.
     * Simple pagination is more efficient for large datasets as it doesn't count total records.
     *
     * @param Builder $query The query builder instance
     * @param array $queryString An array containing filter, sort, and pagination parameters
     * @param int|null $defaultLimit The default number of items per page (null to use config)
     * @return Paginator The simple paginated filtered results
     * @throws ValidationException If the query string contains invalid parameters
     */
    public function scopeFilterSimplePaginate(Builder $query, array $queryString, $defaultLimit = null): Paginator
    {
        return $this->paginateFiltered($query, $queryString, $defaultLimit, 'simplePaginate');
    }

    /**
     * Apply filters to the query and return the count of matching records.
     * Useful for getting totals without retrieving the actual data.
     *
     * @param Builder $query The query builder instance
     * @param array $queryString An array containing filter parameters (sorts are ignored for counting)
     * @return int The count of filtered results
     * @throws ValidationException If the query string contains invalid parameters
     */
    public function scopeFilterCount(Builder $query, array $queryString): int
    {
        // For counting, we only apply filters, not sorts (sorts don't affect count)
        $filtersOnly = array_intersect_key($queryString, array_flip(['filters']));

        return $this->scopeFilter($query, $filtersOnly)->count();
    }

    /**
     * @param string $method Either 'paginate' or 'simplePaginate'
     * @throws ValidationException If the query string contains invalid parameters
     */
    protected function paginateFiltered(Builder $query, array $queryString, $defaultLimit, string $method): LengthAwarePaginator|Paginator
    {
        $maxLimit = config('filterer.pagination.max_limit');

        // Cap numeric limits only; non-numeric values must still fail validation
        if ($maxLimit && isset($queryString['limit']) && is_numeric($queryString['limit']) && $queryString['limit'] > $maxLimit) {
            $queryString['limit'] = (int) $maxLimit;
        }

        $perPage = $queryString['limit'] ?? $defaultLimit ?? config('filterer.pagination.default_limit', 10);
        $page = $queryString['page'] ?? null;
        $pageName = config('filterer.pagination.page_name', 'page');

        return $this->scopeFilter($query, $queryString)
            ->{$method}($perPage, ['*'], $pageName, $page);
    }

    protected function validateQueryString(array $queryString): void
    {
        $maxLimit = config('filterer.pagination.max_limit');
        $customMessages = config('filterer.validation.error_messages', []);

        $rules = [
            // TODO: 'filter_match' => 'sometimes|required|in:and,or',
            'filters' => 'sometimes|required|array',
            'filters.*.column' => 'required_with:f.*.column|in:' . $this->allowedFilterables(),
            'filters.*.operator' => 'required_with:f.*.column|in:' . $this->allowedOperators(),
            'filters.*.query_1' => 'required_with:f.*.column',
            'filters.*.query_2' => 'required_if:f.*.operator,between,not_between',
            'sorts' => 'sometimes|required|array',
            'sorts.*.column' => 'required_with:f|in:' . $this->allowedSortable(),
            'sorts.*.direction' => 'required_with:f.*.column',
            'limit' => 'sometimes|integer|min:1' . ($maxLimit ? "|max:$maxLimit" : ''),
            'page' => 'sometimes|integer|min:1'
        ];

        $validator = validator()->make($queryString, $rules, $customMessages);

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
        $defaultOperators = [
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
        ];

        $configOperators = config('filterer.security.allowed_operators', []);

        // Config can only restrict the supported operators, never add new ones
        $operators = empty($configOperators)
            ? $defaultOperators
            : array_intersect($defaultOperators, $configOperators);

        return implode(',', $operators);
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
