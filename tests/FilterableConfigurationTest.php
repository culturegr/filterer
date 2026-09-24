<?php

namespace CultureGr\Filterer\Tests;

use CultureGr\Filterer\Tests\Fixtures\Client;
use Illuminate\Validation\ValidationException;

class FilterableConfigurationTest extends TestCase
{
    /*
    |--------------------------------------------------------------------------
    | Configuration Integration Tests
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function it_uses_config_default_limit_when_not_specified(): void
    {
        config(['filterer.pagination.default_limit' => 15]);
        factory(Client::class, 20)->create();

        $results = Client::filterPaginate([]);

        $this->assertEquals(15, $results->perPage());
    }

    /** @test */
    public function it_respects_config_max_limit(): void
    {
        config(['filterer.pagination.max_limit' => 25]);
        factory(Client::class, 50)->create();

        // Request more than max limit
        $results = Client::filterPaginate(['limit' => 100]);

        // Should be capped at max limit
        $this->assertEquals(25, $results->perPage());
    }

    /** @test */
    public function it_uses_config_page_name(): void
    {
        config(['filterer.pagination.page_name' => 'custom_page']);
        factory(Client::class, 20)->create();

        $results = Client::filterPaginate(['limit' => 5, 'page' => 2]);

        $this->assertEquals('custom_page', $results->getPageName());
        $this->assertStringContainsString('custom_page=3', $results->nextPageUrl());
    }

    /** @test */
    public function it_does_not_cap_limit_by_default(): void
    {
        factory(Client::class, 150)->create();

        $results = Client::filterPaginate(['limit' => 150]);

        $this->assertNull(config('filterer.pagination.max_limit'));
        $this->assertEquals(150, $results->perPage());
        $this->assertCount(150, $results->items());
    }

    /** @test */
    public function it_does_not_enforce_max_limit_in_filter_by_default(): void
    {
        factory(Client::class, 5)->create();

        $results = Client::filter(['limit' => 500])->get();

        $this->assertCount(5, $results);
    }

    /** @test */
    public function it_rejects_limit_above_config_max_limit_in_filter(): void
    {
        config(['filterer.pagination.max_limit' => 25]);

        $this->expectException(ValidationException::class);

        Client::filter(['limit' => 26]);
    }

    /** @test */
    public function it_rejects_non_numeric_limit_instead_of_capping_it(): void
    {
        config(['filterer.pagination.max_limit' => 25]);

        $this->expectException(ValidationException::class);

        Client::filterPaginate(['limit' => 'abc']);
    }

    /** @test */
    public function it_uses_application_translations_for_validation_messages_by_default(): void
    {
        app('translator')->addLines(['validation.integer' => 'Translated :attribute integer'], 'en');

        try {
            Client::filter(['limit' => 'abc']);
            $this->fail('ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertSame(['Translated limit integer'], $e->errors()['limit']);
        }
    }

    /** @test */
    public function it_uses_config_custom_error_messages(): void
    {
        config(['filterer.validation.error_messages' => ['limit.integer' => 'Custom limit message']]);

        try {
            Client::filter(['limit' => 'abc']);
            $this->fail('ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertSame(['Custom limit message'], $e->errors()['limit']);
        }
    }

    /** @test */
    public function it_applies_config_to_simple_paginate_as_well(): void
    {
        config([
            'filterer.pagination.default_limit' => 12,
            'filterer.pagination.max_limit' => 20
        ]);
        factory(Client::class, 30)->create();

        // Test default limit
        $results1 = Client::filterSimplePaginate([]);
        $this->assertEquals(12, $results1->perPage());

        // Test max limit
        $results2 = Client::filterSimplePaginate(['limit' => 50]);
        $this->assertEquals(20, $results2->perPage());
    }

    /** @test */
    public function it_allows_unlimited_when_max_limit_is_null(): void
    {
        config(['filterer.pagination.max_limit' => null]);
        factory(Client::class, 200)->create();

        // Should allow large limits when max_limit is null
        $results = Client::filterPaginate(['limit' => 150]);

        $this->assertEquals(150, $results->perPage());
    }

    /** @test */
    public function it_uses_config_for_allowed_operators(): void
    {
        // Restrict to only basic operators
        config(['filterer.security.allowed_operators' => ['equal_to', 'contains']]);

        factory(Client::class, 10)->create();

        // This should work (allowed operator)
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'name',
                    'operator' => 'equal_to',
                    'query_1' => 'test',
                ],
            ],
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $results);
    }

    /** @test */
    public function it_validates_against_restricted_operators(): void
    {
        // Restrict to only basic operators
        config(['filterer.security.allowed_operators' => ['equal_to', 'contains']]);

        factory(Client::class, 10)->create();

        // This should fail validation (operator not in allowed list)
        $this->expectException(ValidationException::class);

        Client::filter([
            'filters' => [
                [
                    'column' => 'name',
                    'operator' => 'greater_than', // Not in allowed list
                    'query_1' => '5',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_ignores_unsupported_operators_in_config(): void
    {
        config(['filterer.security.allowed_operators' => ['equal_to', 'unsupported_op']]);

        $this->expectException(ValidationException::class);

        Client::filter([
            'filters' => [
                [
                    'column' => 'name',
                    'operator' => 'unsupported_op',
                    'query_1' => 'test',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_uses_default_operators_when_config_is_empty(): void
    {
        // Empty config should use all default operators
        config(['filterer.security.allowed_operators' => []]);

        factory(Client::class, 10)->create();

        // Should work with any default operator
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'age',
                    'operator' => 'greater_than',
                    'query_1' => '18',
                ],
            ],
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $results);
    }

    protected function seedDatabase(): void
    {
        // No specific seeding needed for configuration tests
    }
}
