<?php

namespace CultureGr\Filterer\Tests;

use CultureGr\Filterer\Tests\Fixtures\Order;
use CultureGr\Filterer\Tests\Fixtures\Client;
use CultureGr\Filterer\Tests\Fixtures\Country;
use CultureGr\Filterer\Tests\Fixtures\FavoriteProduct;

class SortsBuilderTest extends TestCase
{
    protected Country $country1;
    protected Country $country2;
    protected Country $country3;
    protected Client $client1;
    protected Client $client2;
    protected Client $client3;
    protected Client $client4;
    protected Order $order1;
    protected Order $order2;
    protected Order $order3;
    protected Order $order4;
    protected Order $order5;
    protected FavoriteProduct $favoriteProduct1;
    protected FavoriteProduct $favoriteProduct2;
    protected FavoriteProduct $favoriteProduct3;
    protected FavoriteProduct $favoriteProduct4;
    protected FavoriteProduct $favoriteProduct5;

    protected function seedDatabase(): void
    {
        $this->country1 = factory(Country::class)->create(['name' => 'GR']);
        $this->country2 = factory(Country::class)->create(['name' => 'UK']);
        $this->country3 = factory(Country::class)->create(['name' => 'DE']);
        $this->client1 = factory(Client::class)->create(['name'=> 'John', 'country_id' => $this->country2->id]);
        $this->client2 = factory(Client::class)->create(['name'=> 'Jane', 'country_id' => $this->country2->id]);
        $this->client3 = factory(Client::class)->create(['name'=> 'Mary', 'country_id' => $this->country1->id]);
        $this->client4 = factory(Client::class)->create(['name'=> 'Bill', 'country_id' => $this->country3->id]);
        $this->order1 = factory(Order::class)->create(['items' => 2, 'client_id' => $this->client1->id]);
        $this->order2 = factory(Order::class)->create(['items' => 4, 'client_id' => $this->client1->id]);
        $this->order3 = factory(Order::class)->create(['items' => 3, 'client_id' => $this->client2->id]);
        $this->order4 = factory(Order::class)->create(['items' => 5, 'client_id' => $this->client3->id]);
        $this->order5 = factory(Order::class)->create(['items' => 1, 'client_id' => $this->client4->id]);
        $this->favoriteProduct1 = factory(FavoriteProduct::class)->create(['price' => 50]);
        $this->favoriteProduct2 = factory(FavoriteProduct::class)->create(['price' => 60]);
        $this->favoriteProduct3 = factory(FavoriteProduct::class)->create(['price' => 70]);
        $this->favoriteProduct4 = factory(FavoriteProduct::class)->create(['price' => 80]);
        $this->favoriteProduct5 = factory(FavoriteProduct::class)->create(['price' => 90]);
        $this->client1->favoriteProducts()->attach([$this->favoriteProduct1->id, $this->favoriteProduct2->id]);
        $this->client2->favoriteProducts()->attach([$this->favoriteProduct3->id, $this->favoriteProduct4->id]);
        $this->client3->favoriteProducts()->attach([$this->favoriteProduct2->id, $this->favoriteProduct3->id]);
        $this->client4->favoriteProducts()->attach([$this->favoriteProduct4->id, $this->favoriteProduct5->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Filtering by numeric fields on model
    |--------------------------------------------------------------------------
    |
    */

    /** @test */
    public function it_sorts_by_field_in_ascending_order(): void
    {
        $results = Client::filter([
            'sorts' =>  [
                [
                    'column' => 'name',
                    'direction' => 'asc',
                ],
            ],
        ])->get();

        self::assertEquals($results->sortBy('name')->pluck('id'), $results->pluck('id'));
    }

    /** @test */
    public function it_sorts_by_field_in_descending_order(): void
    {
        $results = Client::filter([
            'sorts' =>  [
                [
                    'column' => 'name',
                    'direction' => 'desc',
                ],
            ],
        ])->get();

        self::assertEquals($results->sortByDesc('name')->pluck('id'), $results->pluck('id'));
    }

    /** @test */
    public function it_sorts_by_field_that_exists_in_one_to_many_related_model_in_ascending_order(): void
    {
        $results = Client::filter([
            'sorts' =>  [
                [
                    'column' => 'country.name',
                    'direction' => 'asc',
                ],
            ],
        ])->get();

        self::assertEquals($results->sortBy(fn ($client) => $client->country->name)->pluck('id'), $results->pluck('id'));
    }

    /** @test */
    public function it_sorts_by_field_that_exists_in_one_to_many_related_model_in_descending_order(): void
    {
        $results = Client::filter([
            'sorts' =>  [
                [
                    'column' => 'country.name',
                    'direction' => 'desc',
                ],
            ],
        ])->get();

        self::assertEquals($results->sortByDesc(fn ($client) => $client->country->name)->pluck('id'), $results->pluck('id'));
    }

    /** @test */
    public function it_sorts_by_field_that_exists_in_many_to_one_related_model_in_ascending_order(): void
    {
        $results = Client::filter([
            'sorts' =>  [
                [
                    'column' => 'orders.items',
                    'direction' => 'asc',
                ],
            ],
        ])->get();

        self::assertEquals([
            $this->client4->id,
            $this->client1->id,
            $this->client2->id,
            $this->client3->id,
        ], $results->pluck('id')->all());
    }

    /** @test */
    public function it_sorts_by_field_that_exists_in_many_to_one_related_model_in_descending_order(): void
    {
        $results = Client::filter([
            'sorts' =>  [
                [
                    'column' => 'orders.items',
                    'direction' => 'desc',
                ],
            ],
        ])->get();

        self::assertEquals([
            $this->client3->id,
            $this->client1->id,
            $this->client2->id,
            $this->client4->id,
        ], $results->pluck('id')->all());
    }

    /** @test */
    public function it_sorts_by_field_that_exists_in_many_to_many_related_model_in_ascending_order(): void
    {
        $results = Client::filter([
            'sorts' =>  [
                [
                    'column' => 'favoriteProducts.price',
                    'direction' => 'asc',
                ],
            ],
        ])->get();

        self::assertEquals([
            $this->client1->id,
            $this->client3->id,
            $this->client2->id,
            $this->client4->id,
        ], $results->pluck('id')->all());
    }

    /** @test */
    public function it_sorts_by_field_that_exists_in_many_to_many_related_model_in_descending_order(): void
    {
        $results = Client::filter([
            'sorts' =>  [
                [
                    'column' => 'favoriteProducts.price',
                    'direction' => 'desc',
                ],
            ],
        ])->get();

        self::assertEquals([
            $this->client4->id,
            $this->client2->id,
            $this->client3->id,
            $this->client1->id,
        ], $results->pluck('id')->all());
    }

    /** @test */
    public function it_sorts_by_multiple_fields(): void
    {
        $results = Client::filter([
            'sorts' =>  [
                [
                    'column' => 'name',
                    'direction' => 'asc',
                ],
                [
                    'column' => 'country.name',
                    'direction' => 'desc',
                ],
                [
                    'column' => 'orders.items',
                    'direction' => 'desc',
                ],
                [
                    'column' => 'favoriteProducts.price',
                    'direction' => 'asc',
                ],
            ],
        ])->get();

        self::assertEquals([
            $this->client4->id,
            $this->client2->id,
            $this->client1->id,
            $this->client3->id,
        ], $results->pluck('id')->all());
    }
}
