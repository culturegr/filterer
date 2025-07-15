<?php

namespace CultureGr\Filterer\Tests;

use CultureGr\Filterer\Tests\Fixtures\Client;
use Illuminate\Pagination\LengthAwarePaginator;

class FilterablePaginationTest extends TestCase
{
    /*
    |--------------------------------------------------------------------------
    | Core Pagination Functionality Tests
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function it_adds_filterPaginate_scope_to_models_with_trait(): void
    {
        $client = factory(Client::class)->create();

        $this->assertTrue(method_exists($client, 'scopeFilterPaginate'));
    }

    /** @test */
    public function it_returns_paginator_for_filterPaginate_scope(): void
    {
        factory(Client::class, 10)->create();

        $results = Client::filterPaginate([]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $results);
    }

    /** @test */
    public function it_respects_query_string_limit_in_filterPaginate(): void
    {
        factory(Client::class, 10)->create();

        $results = Client::filterPaginate([
            'limit' => 5,
        ]);

        $this->assertCount(5, $results->items());
    }

    /** @test */
    public function it_uses_defaultLimit_when_query_limit_not_set_in_filterPaginate(): void
    {
        factory(Client::class, 10)->create();
        $defaultLimit = 8;

        $results = Client::filterPaginate([], $defaultLimit);

        $this->assertCount(8, $results->items());
    }

    /** @test */
    public function it_defaults_to_ten_items_when_no_limit_specified_in_filterPaginate(): void
    {
        factory(Client::class, 20)->create();

        $results = Client::filterPaginate([]);

        $this->assertCount(10, $results->items());
    }

    /*
    |--------------------------------------------------------------------------
    | Explicit Page Parameter Handling Tests
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function it_handles_explicit_page_parameter()
    {
        factory(Client::class, 20)->create();

        $filters = [
            'page' => 2,
            'limit' => 5,
        ];

        $result = Client::filterPaginate($filters);

        $this->assertEquals(2, $result->currentPage());
        $this->assertEquals(5, $result->perPage());
    }
    
    /** @test */
    public function it_handles_cookie_persistence_scenario()
    {
        factory(Client::class, 30)->create();
        
        // Test that page parameter from array works correctly
        $cookieData = [
            'page' => 3,
            'limit' => 10,
        ];
        
        $result = Client::filterPaginate($cookieData);
        
        $this->assertEquals(3, $result->currentPage());
    }
    
    /** @test */
    public function it_preserves_laravel_automatic_discovery()
    {
        factory(Client::class, 40)->create();
        
        // Simulate request with page parameter
        request()->merge(['page' => 4]);
        
        // Call filterPaginate without page in array
        $result = Client::filterPaginate([
            'limit' => 10,
        ]);
        
        // Should use Laravel's automatic discovery
        $this->assertEquals(4, $result->currentPage());
    }
    

    
    /** @test */
    public function it_maintains_backward_compatibility()
    {
        factory(Client::class, 15)->create();
        
        // Old usage without any pagination parameters should still work
        $result = Client::filterPaginate([
            'filters' => [
                [
                    'column' => 'name',
                    'operator' => 'contains',
                    'query_1' => 'test',
                    'query_2' => null
                ]
            ]
        ]);
        
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(10, $result->perPage()); // default limit
    }
    
    /** @test */
    public function it_handles_page_parameter_only()
    {
        factory(Client::class, 25)->create();
        
        $result = Client::filterPaginate(['page' => 2]);
        
        $this->assertEquals(2, $result->currentPage());
        $this->assertEquals(10, $result->perPage()); // default limit
    }
    
    /** @test */
    public function it_handles_limit_parameter_only()
    {
        factory(Client::class, 25)->create();
        
        $result = Client::filterPaginate(['limit' => 15]);
        
        $this->assertEquals(1, $result->currentPage()); // default page
        $this->assertEquals(15, $result->perPage());
    }
    

    
    /** @test */
    public function it_combines_filters_with_pagination()
    {
        // Create clients with different ages
        factory(Client::class, 5)->create(['age' => 25]);
        factory(Client::class, 10)->create(['age' => 30]);
        factory(Client::class, 15)->create(['age' => 35]);
        
        $result = Client::filterPaginate([
            'page' => 2,
            'limit' => 5,
            'filters' => [
                [
                    'column' => 'age',
                    'operator' => 'greater_than',
                    'query_1' => '28',
                    'query_2' => null
                ]
            ]
        ]);
        
        $this->assertEquals(2, $result->currentPage());
        $this->assertEquals(5, $result->perPage());
        // Should have filtered results (age > 28) and be on page 2
        $this->assertTrue($result->total() > 0);
    }
    
    /*
    |--------------------------------------------------------------------------
    | Edge Cases and Advanced Scenarios
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function it_handles_large_page_numbers()
    {
        factory(Client::class, 10)->create();
        
        // Request page that doesn't exist
        $result = Client::filterPaginate([
            'page' => 999,
            'limit' => 5
        ]);
        
        $this->assertEquals(999, $result->currentPage());
        $this->assertEquals(5, $result->perPage());
        $this->assertCount(0, $result->items()); // No items on non-existent page
    }
    
    /** @test */
    public function it_works_with_custom_default_limit()
    {
        factory(Client::class, 20)->create();
        
        $result = Client::filterPaginate(['page' => 2], 7); // custom default limit
        
        $this->assertEquals(2, $result->currentPage());
        $this->assertEquals(7, $result->perPage());
    }
    
    /** @test */
    public function it_handles_mixed_url_and_stored_parameters()
    {
        factory(Client::class, 30)->create();
        
        // Simulate URL parameter
        request()->merge(['page' => 3]);
        
        // Stored data with different page
        $storedData = [
            'page' => 1, // This should be overridden by URL parameter
            'limit' => 8,
        ];
        
        // Merge URL parameters over stored data (common pattern)
        $filters = array_merge($storedData, request()->query());
        
        $result = Client::filterPaginate($filters);
        
        $this->assertEquals(3, $result->currentPage()); // URL parameter wins
        $this->assertEquals(8, $result->perPage()); // Stored limit used
    }
    

    
    /** @test */
    public function it_handles_empty_result_set_with_pagination()
    {
        // No clients created

        $result = Client::filterPaginate([
            'page' => 1,
            'limit' => 10
        ]);

        $this->assertEquals(1, $result->currentPage());
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(0, $result->total());
        $this->assertCount(0, $result->items());
    }

    protected function seedDatabase(): void
    {
        // No seeding needed for pagination tests
    }
}
