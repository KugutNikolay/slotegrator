<?php

namespace Modules\Cart\Services;

use Modules\Cart\Contracts\Printable;
use Modules\Cart\Contracts\Storable;
use Modules\Cart\Contracts\Updatable;
use Modules\Cart\Models\Item;
use Modules\Cart\Models\Order;

class OrderService implements Printable, Storable, Updatable
{
    protected Order $order;
    protected Storable $storage;

    public function __construct(Storable $storage) {
        $this->storage = $storage;
        $this->order = $this->load();
    }

    public function addItem(Item $item): void {
        $this->order->addItem($item);
        $this->save($this->order);
    }

    public function deleteItem(int $itemId): void {
        $this->order->deleteItem($itemId);
        $this->save($this->order);
    }

    public function showOrder(): void
    {
        foreach ($this->order->items as $item) {
            echo "{$item->name} x {$item->quantity} = {$item->getTotalPrice()}\n";
        }
        echo "Итого: " . $this->order->calculateTotalSum() . "\n";
    }

    public function printOrder(): void
    {
        $this->showOrder();
    }

    public function save(Order $order): void
    {
        $this->storage->save($order);
    }

    public function load(): Order
    {
        return $this->storage->load();
    }

    public function delete(): void
    {
        $this->storage->delete();
    }

    public function update(): void
    {

    }
}
