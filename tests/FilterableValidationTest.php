<?php

namespace CultureGr\Filterer\Tests;

use CultureGr\Filterer\Tests\Fixtures\Client;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;

class FilterableValidationTest extends TestCase
{
    /*
    |--------------------------------------------------------------------------
    | Filter Field Validation Tests
    |--------------------------------------------------------------------------
    */

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
    public function it_allows_valid_filter_parameters(): void
    {
        factory(Client::class, 5)->create(['age' => 25]);

        // Should not throw validation exception
        $result = Client::filter([
            'filters' => [
                [
                    'column' => 'age',
                    'operator' => 'equal_to',
                    'query_1' => '25',
                    'query_2' => null,
                ],
            ],
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $result);
    }

    /*
    |--------------------------------------------------------------------------
    | Sort Field Validation Tests
    |--------------------------------------------------------------------------
    */

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
    public function it_allows_valid_sort_parameters(): void
    {
        factory(Client::class, 5)->create();

        // Should not throw validation exception
        $result = Client::filter([
            'sorts' => [
                [
                    'column' => 'name',
                    'direction' => 'asc',
                ],
            ],
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $result);
    }

    /*
    |--------------------------------------------------------------------------
    | Pagination Parameter Validation Tests
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function it_validates_limit_parameter_must_be_positive_integer(): void
    {
        $this->expectException(ValidationException::class);

        Client::filterPaginate([
            'limit' => -1
        ]);
    }

    /** @test */
    public function it_validates_limit_parameter_must_be_integer(): void
    {
        $this->expectException(ValidationException::class);

        Client::filterPaginate([
            'limit' => 'invalid'
        ]);
    }

    /** @test */
    public function it_validates_page_parameter_must_be_positive_integer(): void
    {
        $this->expectException(ValidationException::class);

        Client::filterPaginate([
            'page' => 0
        ]);
    }

    /** @test */
    public function it_validates_page_parameter_must_be_integer(): void
    {
        $this->expectException(ValidationException::class);

        Client::filterPaginate([
            'page' => 'invalid'
        ]);
    }

    /** @test */
    public function it_validates_both_limit_and_page_parameters_together(): void
    {
        $this->expectException(ValidationException::class);

        Client::filterPaginate([
            'limit' => -5,
            'page' => 'not_a_number'
        ]);
    }

    /** @test */
    public function it_validates_zero_and_negative_pagination_values(): void
    {
        $this->expectException(ValidationException::class);

        Client::filterPaginate([
            'page' => 0,
            'limit' => 0
        ]);
    }

    /** @test */
    public function it_validates_string_pagination_parameters(): void
    {
        $this->expectException(ValidationException::class);

        Client::filterPaginate([
            'page' => 'two',
            'limit' => 'ten'
        ]);
    }

    /** @test */
    public function it_validates_mixed_invalid_pagination_parameters(): void
    {
        $this->expectException(ValidationException::class);

        Client::filterPaginate([
            'page' => 'invalid',
            'limit' => -1
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Valid Parameter Tests (Positive Validation)
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function it_allows_valid_pagination_parameters(): void
    {
        factory(Client::class, 20)->create();

        // Should not throw validation exception
        $result = Client::filterPaginate([
            'limit' => 5,
            'page' => 2
        ]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(5, $result->perPage());
        $this->assertEquals(2, $result->currentPage());
    }

    /** @test */
    public function it_allows_pagination_parameters_to_be_omitted(): void
    {
        factory(Client::class, 10)->create();

        // Should not throw validation exception when pagination params are omitted
        $result = Client::filterPaginate([
            'filters' => [
                [
                    'column' => 'age',
                    'operator' => 'greater_than',
                    'query_1' => '18',
                    'query_2' => null
                ]
            ]
        ]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /** @test */
    public function it_allows_filters_and_sorts_to_be_omitted(): void
    {
        factory(Client::class, 10)->create();

        // Should not throw validation exception when filters/sorts are omitted
        $result = Client::filter([]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $result);
    }

    /*
    |--------------------------------------------------------------------------
    | Combined Validation Tests
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function it_validates_combined_filters_sorts_and_pagination(): void
    {
        factory(Client::class, 20)->create();

        // Should not throw validation exception with valid combined parameters
        $result = Client::filterPaginate([
            'filters' => [
                [
                    'column' => 'age',
                    'operator' => 'greater_than',
                    'query_1' => '18',
                    'query_2' => null
                ]
            ],
            'sorts' => [
                [
                    'column' => 'name',
                    'direction' => 'asc',
                ],
            ],
            'limit' => 5,
            'page' => 2
        ]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    protected function seedDatabase(): void
    {
        // No seeding needed for validation tests
    }
}
