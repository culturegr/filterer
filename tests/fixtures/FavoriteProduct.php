<?php

namespace CultureGr\Filterer\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class FavoriteProduct extends Model
{
    protected $guarded = [];

    public function clients()
    {
        return $this->belongsToMany(Client::class);
    }
}
