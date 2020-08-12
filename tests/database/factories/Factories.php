<?php

use Faker\Generator as Faker;
use CultureGr\Filterer\Tests\Fixtures\Order;
use CultureGr\Filterer\Tests\Fixtures\Client;
use CultureGr\Filterer\Tests\Fixtures\Country;
use CultureGr\Filterer\Tests\Fixtures\FavoriteProduct;

$factory->define(Country::class, function (Faker $faker) {
    return [
        'name' => $faker->country,
    ];
});

$factory->define(Client::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'age' => $faker->numberBetween(18, 65),
        'registered_at' => $faker->dateTimeBetween('-1 year', '-2 months'),
        'country_id' => factory(Country::class),
    ];
});

$factory->define(Order::class, function (Faker $faker) {
    return [
        'reference_code' => $faker->regexify('[A-Z0-9]{8}'),
        'items' => $faker->numberBetween(1, 10),
        'shipped_at' => $faker->dateTimeBetween('-1 month', '-1 week'),
        'client_id' => factory(Client::class),
    ];
});

$factory->define(FavoriteProduct::class, function (Faker $faker) {
    return [
        'name' => $faker->randomElement(['Apples', 'Oranges', 'Berries', 'Bananas']),
        'price' => $faker->numberBetween(1, 10),
    ];
});
