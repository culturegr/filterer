<?php

namespace CultureGr\Filterer\Tests;

use Illuminate\Database\Eloquent\Builder;
use CultureGr\Filterer\Tests\Fixtures\Client;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class FiltererTest extends TestCase
{
    /*
    |--------------------------------------------------------------------------
    | Core Trait Functionality Tests
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function it_adds_filter_scope_to_models_with_trait(): void
    {
        $client = factory(Client::class)->create();

        $this->assertTrue(method_exists($client, 'scopeFilter'));
    }

    /** @test */
    public function it_adds_filterPaginate_scope_to_models_with_trait(): void
    {
        $client = factory(Client::class)->create();

        $this->assertTrue(method_exists($client, 'scopeFilterPaginate'));
    }

    /** @test */
    public function it_adds_filterSimplePaginate_scope_to_models_with_trait(): void
    {
        $client = factory(Client::class)->create();

        $this->assertTrue(method_exists($client, 'scopeFilterSimplePaginate'));
    }

    /** @test */
    public function it_adds_filterCount_scope_to_models_with_trait(): void
    {
        $client = factory(Client::class)->create();

        $this->assertTrue(method_exists($client, 'scopeFilterCount'));
    }

    /*
    |--------------------------------------------------------------------------
    | Basic Integration Tests
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function it_returns_builder_for_filter_scope(): void
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

        $this->assertInstanceOf(Builder::class, $results);
    }

    /** @test */
    public function it_returns_paginator_for_filterPaginate_scope(): void
    {
        factory(Client::class, 10)->create();

        $results = Client::filterPaginate([
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
            'limit' => 5,
            'page' => 1,
        ]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $results);
    }

    /** @test */
    public function it_works_with_empty_parameters(): void
    {
        factory(Client::class, 5)->create();

        // Should work with empty array
        $filterResults = Client::filter([]);
        $this->assertInstanceOf(Builder::class, $filterResults);

        // Should work with empty array
        $paginateResults = Client::filterPaginate([]);
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginateResults);
    }

    /** @test */
    public function it_maintains_method_chaining(): void
    {
        factory(Client::class, 15)->create();

        // Test that filter() returns a Builder that can be chained
        $results = Client::filter([])->limit(3)->get();

        $this->assertCount(3, $results);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods Tests
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function it_returns_simple_paginator_for_filterSimplePaginate_scope(): void
    {
        factory(Client::class, 20)->create();

        $results = Client::filterSimplePaginate([
            'limit' => 5,
            'page' => 2,
        ]);

        $this->assertInstanceOf(Paginator::class, $results);
        $this->assertEquals(5, $results->perPage());
        $this->assertCount(5, $results->items());
    }

    /** @test */
    public function it_returns_count_for_filterCount_scope(): void
    {
        factory(Client::class, 3)->create(['age' => 20]);
        factory(Client::class, 4)->create(['age' => 30]);

        $count = Client::filterCount([
            'filters' => [
                [
                    'column' => 'age',
                    'operator' => 'greater_than',
                    'query_1' => '20',
                    'query_2' => null,
                ],
            ],
        ]);

        $this->assertSame(4, $count);
    }

    /** @test */
    public function it_ignores_sorts_in_filterCount_for_efficiency(): void
    {
        factory(Client::class, 10)->create();

        // Count should be the same regardless of sorts
        $countWithoutSorts = Client::filterCount([]);

        $countWithSorts = Client::filterCount([
            'sorts' => [
                [
                    'column' => 'name',
                    'direction' => 'asc',
                ],
            ],
        ]);

        $this->assertEquals($countWithoutSorts, $countWithSorts);
        $this->assertEquals(10, $countWithoutSorts);
    }

    /** @test */
    public function it_works_with_empty_parameters_for_helper_methods(): void
    {
        factory(Client::class, 8)->create();

        // Test filterSimplePaginate with empty array
        $simplePaginateResults = Client::filterSimplePaginate([]);
        $this->assertInstanceOf(Paginator::class, $simplePaginateResults);

        // Test filterCount with empty array
        $count = Client::filterCount([]);
        $this->assertEquals(8, $count);
    }



    protected function seedDatabase(): void
    {
        // No seeding needed for core functionality tests
    }
}
