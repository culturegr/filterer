<?php

namespace CultureGr\Filterer\Providers;

use CultureGr\Filterer\Commands\MakeCustomFilterCommand;
use Illuminate\Support\ServiceProvider;

class FiltererServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([MakeCustomFilterCommand::class]);
    }
}
