<?php

namespace CultureGr\Filterer\Tests;

use Illuminate\Database\Eloquent\Builder;
use CultureGr\Filterer\Tests\Fixtures\Client;
use Illuminate\Pagination\LengthAwarePaginator;

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

    protected function seedDatabase(): void
    {
        // No seeding needed for core functionality tests
    }
}
