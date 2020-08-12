<?php

namespace CultureGr\Filterer\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $guarded = [];

    public function client()
    {
        return $this->hasMany(Client::class);
    }
}
