<?php

namespace App\Actions;

use App\Contracts\Services\PaymentGatewayInterface;
use App\Models\Invoice;
use App\Models\User;

class CreateInvoiceAction
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
     * @param User $user
     * @param int  $amount
     *
     * @return Invoice
     */
    public function execute(User $user, int $amount)
    {
        $gatewayInvoice = $this
            ->gateway
            ->createInvoice($amount);

        return Invoice::create([
            'provider_id' => $gatewayInvoice->getIdentifier(),
            'amount' => $gatewayInvoice->getAmount(),
            'user_id' => $user->id,
        ]);
    }
}
