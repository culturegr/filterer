<?php

namespace CultureGr\Filterer\Tests\Fixtures;

use CultureGr\Filterer\Filterable;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use Filterable;

    protected $guarded = [];

    protected $filterable = ['age', 'name', 'registered_at', 'created_at', 'country.name', 'orders.shipped_at', 'favoriteProducts.price'];

    protected $sortable = ['name', 'country.name', 'orders.items', 'favoriteProducts.price'];

    protected $casts = [
        'registered_at' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function favoriteProducts()
    {
        return $this->belongsToMany(FavoriteProduct::class);
    }
}
