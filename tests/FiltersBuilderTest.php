<?php

namespace CultureGr\Filterer\Tests;

use CultureGr\Filterer\Tests\Fixtures\Order;
use CultureGr\Filterer\Tests\Fixtures\Client;
use CultureGr\Filterer\Tests\Fixtures\Country;
use CultureGr\Filterer\Tests\Fixtures\FavoriteProduct;

class FiltersBuilderTest extends TestCase
{
    protected Country $country1;
    protected Country $country2;
    protected Client $client1;
    protected Client $client2;
    protected Client $client3;
    protected Order $order1;
    protected Order $order2;
    protected Order $order3;
    protected FavoriteProduct $favoriteProduct1;
    protected FavoriteProduct $favoriteProduct2;
    protected FavoriteProduct $favoriteProduct3;

    protected function seedDatabase(): void
    {
        $this->country1 = factory(Country::class)->create(['name' => 'GR']);
        $this->country2 = factory(Country::class)->create(['name' => 'UK']);
        $this->client1 = factory(Client::class)->create(['name'=> 'John', 'age' => 25, 'registered_at' => '2018-10-05', 'country_id' => $this->country2->id, 'created_at' => '1970-01-01T00:00:00Z']);
        $this->client2 = factory(Client::class)->create(['name'=> 'Jane', 'age' => 30, 'registered_at' => '2018-08-05', 'country_id' => $this->country2->id]);
        $this->client3 = factory(Client::class)->create(['name'=> 'Mary', 'age' => 25, 'registered_at' => '2018-04-05', 'country_id' => $this->country1->id]);
        $this->order1 = factory(Order::class)->create(['shipped_at' => '2019-08-15 09:30:00', 'client_id' => $this->client1->id]);
        $this->order2 = factory(Order::class)->create(['shipped_at' => '2019-06-15 09:30:00', 'client_id' => $this->client1->id]);
        $this->order3 = factory(Order::class)->create(['shipped_at' => '2019-04-15 09:30:00', 'client_id' => $this->client2->id]);
        $this->favoriteProduct1 = factory(FavoriteProduct::class)->create(['price' => 5]);
        $this->favoriteProduct2 = factory(FavoriteProduct::class)->create(['price' => 6]);
        $this->favoriteProduct3 = factory(FavoriteProduct::class)->create(['price' => 7]);
        $this->client1->favoriteProducts()->attach([$this->favoriteProduct1->id, $this->favoriteProduct2->id]);
        $this->client2->favoriteProducts()->attach([$this->favoriteProduct1->id, $this->favoriteProduct3->id]);
        $this->client3->favoriteProducts()->attach($this->favoriteProduct2->id);
    }

    /*
    |--------------------------------------------------------------------------
    | Filtering by numeric fields on model
    |--------------------------------------------------------------------------
    |
    */

    /** @test */
    public function it_filters_by_numeric_field_equal_to_value(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'age',
                    'operator' => 'equal_to',
                    'query_1' => '25',
                    'query_2' => null,
                ],
            ],
        ])->get();

        self::assertCount(2, $results);
        self::assertTrue($results->contains($this->client1));
        self::assertNotTrue($results->contains($this->client2));
        self::assertTrue($results->contains($this->client3));
    }

    /** @test */
    public function it_filters_by_numeric_field_not_equal_to_value(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'age',
                    'operator' => 'not_equal_to',
                    'query_1' => '25',
                    'query_2' => null,
                ],
            ],
        ])->get();

        self::assertCount(1, $results);
        self::assertNotTrue($results->contains($this->client1));
        self::assertTrue($results->contains($this->client2));
        self::assertNotTrue($results->contains($this->client3));
    }

    /** @test */
    public function it_filters_by_numeric_field_less_than_value(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'age',
                    'operator' => 'less_than',
                    'query_1' => '30',
                    'query_2' => null,
                ],
            ],
        ])->get();

        self::assertCount(2, $results);
        self::assertTrue($results->contains($this->client1));
        self::assertNotTrue($results->contains($this->client2));
        self::assertTrue($results->contains($this->client3));
    }

    /** @test */
    public function it_filters_by_numeric_field_greater_than_value(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'age',
                    'operator' => 'greater_than',
                    'query_1' => '25',
                    'query_2' => null,
                ],
            ],
        ])->get();

        self::assertCount(1, $results);
        self::assertNotTrue($results->contains($this->client1));
        self::assertTrue($results->contains($this->client2));
        self::assertNotTrue($results->contains($this->client3));
    }

    /** @test */
    public function it_filters_by_numeric_field_between_two_values(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'age',
                    'operator' => 'between',
                    'query_1' => '25',
                    'query_2' => '28',
                ],
            ],
        ])->get();

        self::assertCount(2, $results);
        self::assertTrue($results->contains($this->client1));
        self::assertNotTrue($results->contains($this->client2));
        self::assertTrue($results->contains($this->client3));
    }

    /** @test */
    public function it_filters_by_numeric_field_not_between_two_values(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'age',
                    'operator' => 'not_between',
                    'query_1' => '28',
                    'query_2' => '30',
                ],
            ],
        ])->get();

        self::assertCount(2, $results);
        self::assertTrue($results->contains($this->client1));
        self::assertNotTrue($results->contains($this->client2));
        self::assertTrue($results->contains($this->client3));
    }

    /*
    |--------------------------------------------------------------------------
    | Filtering string fields on model
    |--------------------------------------------------------------------------
    |
    */

    /** @test */
    public function it_filters_by_string_field_equal_to_value(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'name',
                    'operator' => 'equal_to',
                    'query_1' => 'John',
                    'query_2' => null,
                ],
            ],
        ])->get();

        self::assertCount(1, $results);
        self::assertTrue($results->contains($this->client1));
        self::assertNotTrue($results->contains($this->client2));
        self::assertNotTrue($results->contains($this->client3));
    }

    /** @test */
    public function it_filters_by_string_field_not_equal_to_value(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'name',
                    'operator' => 'not_equal_to',
                    'query_1' => 'John',
                    'query_2' => null,
                ],
            ],
        ])->get();

        self::assertCount(2, $results);
        self::assertNotTrue($results->contains($this->client1));
        self::assertTrue($results->contains($this->client2));
        self::assertTrue($results->contains($this->client3));
    }

    /** @test */
    public function it_filters_by_string_field_contains_value(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'name',
                    'operator' => 'contains',
                    'query_1' => 'n',
                    'query_2' => null,
                ],
            ],
        ])->get();

        self::assertCount(2, $results);
        self::assertTrue($results->contains($this->client1));
        self::assertTrue($results->contains($this->client2));
        self::assertNotTrue($results->contains($this->client3));
    }

    /*
    |--------------------------------------------------------------------------
    | Filtering date/datetime fields on model
    |--------------------------------------------------------------------------
    |
    */

    /** @test */
    public function it_filters_by_date_field_between_two_dates(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'registered_at',
                    'operator' => 'between_date',
                    'query_1' => '2018-04-05',
                    'query_2' => '2018-08-05',
                ],
            ],
        ])->get();

        self::assertCount(2, $results);
        self::assertNotTrue($results->contains($this->client1));
        self::assertTrue($results->contains($this->client2));
        self::assertTrue($results->contains($this->client3));
    }

    /** @test */
    public function it_filters_by_datetime_field_between_two_timestamps(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'created_at',
                    'operator' => 'between_date',
                    'query_1' => '1970-01-01 00:00:00',
                    'query_2' => '1970-01-01 10:00:00',
                ],
            ],
        ])->get();

        self::assertCount(1, $results);
        self::assertTrue($results->contains($this->client1));
        self::assertNotTrue($results->contains($this->client2));
        self::assertNotTrue($results->contains($this->client3));
    }

    /*
    |--------------------------------------------------------------------------
    | Filtering by fields on related models
    |--------------------------------------------------------------------------
    |
    */

    /** @test */
    public function it_filters_by_field_that_exists_in_onetomany_related_model(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'country.name',
                    'operator' => 'in',
                    'query_1' => ['UK'],
                    'query_2' => null,
                ],
            ],
        ])->get();

        self::assertCount(2, $results);
        self::assertTrue($results->contains($this->client1));
        self::assertTrue($results->contains($this->client2));
        self::assertNotTrue($results->contains($this->client3));
    }

    /** @test */
    public function it_filters_by_multiple_values_of_field_that_exists_in_one_to_many_related_model(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'country.name',
                    'operator' => 'in',
                    'query_1' => ['UK', 'GR'],
                    'query_2' => null,
                ],
            ],
        ])->get();

        self::assertCount(3, $results);
        self::assertTrue($results->contains($this->client1));
        self::assertTrue($results->contains($this->client2));
        self::assertTrue($results->contains($this->client3));
    }

    /** @test */
    public function it_filters_by_field_that_exists_in_many_to_one_related_model(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'orders.shipped_at',
                    'operator' => 'in',
                    'query_1' => ['2019-08-15 09:30:00'], // i.e ORDER1
                    'query_2' => null,
                ],
            ],
        ])->get();

        self::assertCount(1, $results);
        self::assertTrue($results->contains($this->client1));
        self::assertNotTrue($results->contains($this->client2));
        self::assertNotTrue($results->contains($this->client3));
    }

    /** @test */
    public function it_filters_by_multiple_values_of_field_that_exists_in_many_to_one_related_model(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'orders.shipped_at',
                    'operator' => 'in',
                    'query_1' => ['2019-08-15 09:30:00', '2019-04-15 09:30:00'], // i.e ORDER1 and ORDER3
                    'query_2' => null,
                ],
            ],
        ])->get();

        self::assertCount(2, $results);
        self::assertTrue($results->contains($this->client1));
        self::assertTrue($results->contains($this->client2));
        self::assertNotTrue($results->contains($this->client3));
    }

    /** @test */
    public function it_filters_by_field_that_exists_in_many_to_many_related_model(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'favoriteProducts.price',
                    'operator' => 'in',
                    'query_1' => ['6'], // i.e. Oranges
                    'query_2' => null,
                ],
            ],
        ])->get();

        self::assertCount(2, $results);
        self::assertTrue($results->contains($this->client1));
        self::assertNotTrue($results->contains($this->client2));
        self::assertTrue($results->contains($this->client3));
    }

    /** @test */
    public function it_filters_by_multiple_values_of_field_that_exists_in_many_to_many_related_model(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'favoriteProducts.price',
                    'operator' => 'in',
                    'query_1' => ['5' ,'7'], // i.e Apples and Bananas
                    'query_2' => null,
                ],
            ],
        ])->get();

        self::assertCount(2, $results);
        self::assertTrue($results->contains($this->client1));
        self::assertTrue($results->contains($this->client2));
        self::assertNotTrue($results->contains($this->client3));
    }

    /** @test */
    public function it_combines_multiple_fitlers_using_the_AND_operator(): void
    {
        $results = Client::filter([
            'filters' => [
                [
                    'column' => 'favoriteProducts.price',
                    'operator' => 'in',
                    'query_1' => ['6' ,'7'],
                    'query_2' => null,
                ],
                [
                    'column' => 'orders.shipped_at',
                    'operator' => 'in',
                    'query_1' => ['2019-06-15 09:30:00' ,'2019-04-15 09:30:00'],
                    'query_2' => null,
                ],
                [
                    'column' => 'country.name',
                    'operator' => 'equal_to',
                    'query_1' => 'UK',
                    'query_2' => null,
                ],
                [
                    'column' => 'age',
                    'operator' => 'equal_to',
                    'query_1' => '25',
                    'query_2' => null,
                ],
            ],
        ])->get();

        self::assertCount(1, $results);
        self::assertTrue($results->contains($this->client1));
        self::assertNotTrue($results->contains($this->client2));
        self::assertNotTrue($results->contains($this->client3));
    }
}
