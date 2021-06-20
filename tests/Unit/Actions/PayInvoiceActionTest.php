<?php

namespace Tests\Unit\Actions;

use App\Actions\PayInvoiceAction;
use App\Contracts\Services\PaymentGatewayInterface;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class PayInvoiceActionTest extends TestCase
{
    use DatabaseMigrations;
    use WithFaker;

    /**
     * @var MockInterface|PaymentGatewayInterface
     */
    private $gatewayMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gatewayMock = Mockery::mock(PaymentGatewayInterface::class);
    }

    public function test_it_returns_paid_invoice_if_payment_succeeds()
    {
        $invoice = Invoice
            ::factory()
            ->create();
        $card = [
            'number' => $this->faker->creditCardNumber,
            'cvv' => $this->faker->numerify('###'),
            'expiration' => $this->faker->creditCardExpirationDate,
        ];

        $this->gatewayMock
            ->expects('payInvoice')
            ->once()
            ->with($invoice->provider_id, $card)
            ->andReturn(true);

        $action = new PayInvoiceAction($this->gatewayMock);
        $invoice = $action->execute($invoice, $card);

        $this->assertEquals(Invoice::STATUS_PAID, $invoice->status);
    }

    public function test_it_returns_canceled_invoice_if_payment_fails()
    {
        $invoice = Invoice
            ::factory()
            ->create();
        $card = [
            'number' => $this->faker->creditCardNumber,
            'cvv' => $this->faker->numerify('###'),
            'expiration' => $this->faker->creditCardExpirationDate,
        ];

        $this->gatewayMock
            ->expects('payInvoice')
            ->once()
            ->with($invoice->provider_id, $card)
            ->andReturn(false);

        $action = new PayInvoiceAction($this->gatewayMock);
        $invoice = $action->execute($invoice, $card);

        $this->assertEquals(Invoice::STATUS_CANCELED, $invoice->status);
    }
}
