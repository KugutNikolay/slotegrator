<?php

namespace Modules\Cart\Models;

class Order {

    public array $items = [];

    public function addItem(Item $item): void
    {
        foreach ($this->items as $existingItem) {
            if ($existingItem->id === $item->id) {
                $existingItem->quantity += $item->quantity;
                return;
            }
        }
        $this->items[] = $item;
    }

    public function deleteItem(int $itemId): void
    {
        $this->items = array_filter($this->items, fn($i) => $i->id !== $itemId);
    }

    public function getItemsCount(): int
    {
        return array_reduce($this->items, fn($carry, $item) => $carry + $item->quantity, 0);
    }

    public function calculateTotalSum(): float
    {
        return array_reduce($this->items, fn($carry, $item) => $carry + $item->getTotalPrice(), 0);
    }

    public function clear(): void
    {
        $this->items = [];
    }
}
