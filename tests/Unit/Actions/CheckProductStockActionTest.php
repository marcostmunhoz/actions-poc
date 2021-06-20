<?php

namespace Tests\Unit\Actions;

use App\Actions\CheckProductStockAction;
use App\Models\Product;
use Tests\TestCase;

class CheckProductStockActionTest extends TestCase
{
    public function test_it_throws_exception_if_theres_no_stock()
    {
        $action = new CheckProductStockAction();
        $product = Product
            ::factory()
            ->state(['stock' => 100])
            ->make();

        $action->execute($product, 100);

        $product->stock -= 50;

        $this->expectExceptionMessage('Insufficient stock.');

        $action->execute($product, 100);
    }
}
