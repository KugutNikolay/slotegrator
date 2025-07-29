<?php

namespace Modules\Cart\Strategies;

use Modules\Cart\Contracts\Storable;
use Modules\Cart\Models\Order;

class SessionStorageStrategy implements Storable
{
    protected string $key = 'cart.order';

    public function load(): Order
    {
        return session()->has($this->key) ? unserialize(session($this->key)) : new Order();
    }

    public function save(Order $order): void
    {
        session([$this->key => serialize($order)]);
    }

    public function delete(): void
    {
        session()->forget($this->key);
    }

}
