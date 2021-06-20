<?php

namespace App\Actions;

use App\Models\Invoice;
use App\Models\Order;
use App\Notifications\OrderPaidNotification;
use Exception;

class PayOrderAction
{
    private CreateInvoiceAction $createInvoiceAction;

    private PayInvoiceAction $payInvoiceAction;

    /**
     * @param CreateInvoiceAction $createInvoiceAction
     * @param PayInvoiceAction    $payInvoiceAction
     *
     * @return void
     */
    public function __construct(
        CreateInvoiceAction $createInvoiceAction,
        PayInvoiceAction $payInvoiceAction
    ) {
        $this->createInvoiceAction = $createInvoiceAction;
        $this->payInvoiceAction = $payInvoiceAction;
    }

    /**
     * @param Order $order
     * @param array $card
     *
     * @return Order
     *
     * @throws Exception
     */
    public function execute(Order $order, array $card)
    {
        try {
            $invoice = $this
                ->createInvoiceAction
                ->execute($order->user, $order->total);

            $order->update(['invoice_id' => $invoice->id]);

            $invoice = $this
                ->payInvoiceAction
                ->execute($invoice, $card);

            if (Invoice::STATUS_PAID !== $invoice->status) {
                throw new Exception('Payment error.');
            }

            $order->update(['status' => Order::STATUS_PAID]);

            $order->user->notify(new OrderPaidNotification($order));

            return $order;
        } catch (Exception $e) {
            $order->update(['status' => Order::STATUS_CANCELED]);
            foreach ($order->products as $product) {
                $product->increment('stock', $product->pivot->quantity);
            }

            throw $e;
        }
    }
}
