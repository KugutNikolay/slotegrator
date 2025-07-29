<?php

namespace Modules\Cart\Contracts;

use Modules\Cart\Models\Order;

interface Storable
{
    public function load(): Order;
    public function save(Order $order): void;
    public function delete(): void;
}

