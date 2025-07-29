<?php

namespace Modules\Cart\Contracts;

interface Printable
{
    public function printOrder(): void;
    public function showOrder(): void;
}
