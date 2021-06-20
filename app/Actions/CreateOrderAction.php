<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderCreatedNotification;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    private $checkProductStockAction;

    /**
     * @param CheckProductStockAction $checkProductStockAction
     *
     * @return void
     */
    public function __construct(CheckProductStockAction $checkProductStockAction)
    {
        $this->checkProductStockAction = $checkProductStockAction;
    }

    /**
     * @param User  $user
     * @param array $data array de arrays, com id e quantity de cada produto
     *
     * @return Order
     */
    public function execute(User $user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            $total = 0;
            /** @var Order $order */
            $order = Order::create([
                'user_id' => $user->id,
                'total' => 0,
            ]);

            foreach ($data as ['id' => $id, 'quantity' => $quantity]) {
                $product = Product::findOrFail($id);

                $this->checkProductStockAction->execute($product, $quantity);

                $total += ($product->price * $quantity);

                $order->products()->attach($id, compact('quantity'));

                $product->decrement('stock', $quantity);
            }

            $order->update(compact('total'));

            $user->notify(new OrderCreatedNotification($order));

            return $order;
        });
    }
}
