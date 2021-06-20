<?php

namespace App\Actions;

use App\Contracts\Services\PaymentGatewayInterface;
use App\Models\Invoice;

class PayInvoiceAction
{
    private PaymentGatewayInterface $gateway;

    /**
     * @param PaymentGatewayInterface $gateway
     *
     * @return void
     */
    public function __construct(PaymentGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @param Invoice $invoice
     * @param array   $card
     *
     * @return Invoice
     */
    public function execute(Invoice $invoice, array $card)
    {
        $paid = $this->gateway
            ->payInvoice($invoice->provider_id, $card);

        return tap($invoice)
            ->update([
                'status' => $paid
                    ? Invoice::STATUS_PAID
                    : Invoice::STATUS_CANCELED,
            ]);
    }
}
