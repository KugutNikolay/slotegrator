<?php

namespace Modules\Cart\Models;

class Item
{
    public int $id;
    public string $name;
    public float $price;
    public int $quantity;

    public function __construct(int $id, string $name, float $price, int $quantity = 1)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
    }

    public function getTotalPrice(): float
    {
        return $this->price * $this->quantity;
    }
}
