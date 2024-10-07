<?php

namespace CultureGr\Filterer\Tests;

use Illuminate\Database\Eloquent\Builder;
use CultureGr\Filterer\Tests\Fixtures\Client;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;

class FiltererTest extends TestCase
{
    /** @test */
    public function it_provides_a_filter_scope_to_eloquent_models_using_the_trait(): void
    {
        $client = factory(Client::class)->create();

        self::assertTrue(method_exists($client, 'scopeFilter'));
    }

    /** @test */
    public function it_provides_a_filterPaginate_scope_to_eloquent_models_using_the_trait(): void
    {
        $client = factory(Client::class)->create();

        self::assertTrue(method_exists($client, 'scopeFilterPaginate'));
    }

    /** @test */
    public function it_throws_a_validation_exception_if_filter_field_has_not_been_defined_in_filterable(): void
    {
        $this->expectException(ValidationException::class);

        Client::filter([
            'filters' => [
                [
                    'column' => 'does_not_exists_on_filterable',
                    'operator' => 'equal_to',
                    'query_1' => '2020-08-06T08:07:23.000000Z',
                    'query_2' => null,
                ],
            ],
        ]);
    }

    /** @test */
    public function it_throws_a_validation_exception_if_filter_operator_is_unsupported(): void
    {
        $this->expectException(ValidationException::class);

        Client::filter([
            'filters' => [
                [
                    'column' => 'age',
                    'operator' => 'not_supported',
                    'query_1' => '2020-08-06T08:07:23.000000Z',
                    'query_2' => null,
                ],
            ],
        ]);
    }

    /** @test */
    public function it_throws_a_validation_exception_if_sort_field_has_not_been_defined_in_sortables(): void
    {
        $this->expectException(ValidationException::class);

        Client::filter([
            'sorts' => [
                [
                    'column' => 'does_not_exists_on_sortable',
                    'direction' => 'asc',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_returns_custom_builder_instance_if_filter_scope_is_called(): void
    {
        factory(Client::class, 10)->create();

        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'name',
                    'operator' => 'equal_to',
                    'query_1' => 'John',
                    'query_2' => null,
                ],
            ],
            'sorts' => [
                [
                    'column' => 'name',
                    'direction' => 'asc',
                ],
            ],
        ]);

        self::assertInstanceOf(Builder::class, $results);
    }

    /** @test */
    public function it_returns_paginator_instance_if_filterPaginate_scope_is_called(): void
    {
        factory(Client::class, 10)->create();

        $results = Client::filterPaginate([]);

        self::assertInstanceOf(LengthAwarePaginator::class, $results);
    }

    /** @test */
    public function filterPaginate_scope_returns_paginated_results_according_to_limit_query_string_parameter(): void
    {
        factory(Client::class, 10)->create();

        $results = Client::filterPaginate([
            'limit' => 5,
        ]);

        self::assertCount(5, $results->items());
    }

    /** @test */
    public function filterPaginate_scope_returns_paginated_results_according_to_defaultLimit_parameter_if_limit_query_string_parameter_is_not_set(): void
    {
        factory(Client::class, 10)->create();
        $defaultLimit = 8;

        $results = Client::filterPaginate([], $defaultLimit);

        self::assertCount(8, $results->items());
    }

    /** @test */
    public function filterPaginate_scope_returns_paginated_results_by_ten_if_neither_limit_query_string_parameter_nor_defaultLimit_parameter_are_set(): void
    {
        factory(Client::class, 20)->create();

        $results = Client::filterPaginate([]);

        self::assertCount(10, $results->items());
    }

    protected function seedDatabase(): void
    {
    }
}
