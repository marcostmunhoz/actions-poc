<?php

namespace App\Http\Controllers\Api;

use App\Actions\PayOrderAction;
use App\CreateOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\PayRequest;
use App\Http\Requests\Order\StoreRequest;
use App\Models\Order;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * @return Collection<mixed, Order>
     */
    public function index()
    {
        $orders = Order
            ::with('products')
            ->get();

        return $orders;
    }

    /**
     * @param Order $order
     *
     * @return Order
     */
    public function show(Order $order)
    {
        return $order->load('products');
    }

    /**
     * @param StoreRequest      $request
     * @param CreateOrderAction $action
     *
     * @return Order
     *
     * @throws ValidationException
     * @throws BindingResolutionException
     */
    public function store(StoreRequest $request, CreateOrderAction $action)
    {
        $data = $request->validated();
        try {
            $order = $action->execute(
                auth()->user(),
                $data['products']
            );
        } catch (Exception $e) {
            abort(400, $e->getMessage());
        }

        return $order;
    }

    /**
     * @param Order          $order
     * @param PayRequest     $request
     * @param PayOrderAction $action
     *
     * @return Order
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function pay(Order $order, PayRequest $request, PayOrderAction $action)
    {
        $data = $request->validated();
        try {
            $order = $action->execute(
                $order,
                $data['card']
            );
        } catch (Exception $e) {
            abort(400, $e->getMessage());
        }

        return $order;
    }
}
