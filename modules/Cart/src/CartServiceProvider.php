<?php

namespace Modules\Cart;

use Illuminate\Support\ServiceProvider;
use Modules\Cart\Contracts\Storable;
use Modules\Cart\Strategies\SessionStorageStrategy;

class CartServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind(Storable::class, SessionStorageStrategy::class);
    }
}
