<?php

namespace CultureGr\Filterer\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    protected $dates = ['shipped_at'];

    public function permissions()
    {
        return $this->belongsTo(Client::class);
    }
}
