<?php

namespace App\Actions;

use App\Models\Product;
use Exception;

class CheckProductStockAction
{
    /**
     * @param Product $product
     * @param int     $quantity
     *
     * @return void
     *
     * @throws Exception
     */
    public function execute(Product $product, int $quantity)
    {
        if ($product->stock < $quantity) {
            throw new Exception('Insufficient stock.');
        }
    }
}
