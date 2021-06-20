<?php

namespace Tests\Unit\Actions;

use App\Actions\CreateInvoiceAction;
use App\Actions\PayInvoiceAction;
use App\Actions\PayOrderAction;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderPaidNotification;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class PayOrderActionTest extends TestCase
{
    use DatabaseMigrations;
    use WithFaker;

    /**
     * @var MockInterface|CreateInvoiceAction
     */
    private $createInvoiceActionMock;

    /**
     * @var MockInterface|PayInvoiceAction
     */
    private $payInvoiceActionMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createInvoiceActionMock = Mockery::mock(CreateInvoiceAction::class);
        $this->payInvoiceActionMock = Mockery::mock(PayInvoiceAction::class);

        Notification::fake();
    }

    public function test_it_returns_paid_order_if_payment_succeeds()
    {
        $user = User
            ::factory()
            ->create();
        $order = Order
            ::factory()
            ->for($user)
            ->hasAttached(
                Product::factory()->state(['price' => 100]),
                ['quantity' => 1]
            )
            ->create();
        $card = [
            'number' => $this->faker->creditCardNumber,
            'cvv' => $this->faker->numerify('###'),
            'expiration' => $this->faker->creditCardExpirationDate,
        ];

        $this->createInvoiceActionMock
            ->expects('execute')
            ->once()
            ->andReturn(
                $invoice = Invoice
                    ::factory()
                    ->for($user)
                    ->state(['amount' => 100])
                    ->create()
            );
        $this->payInvoiceActionMock
            ->expects('execute')
            ->once()
            ->with($invoice, $card)
            ->andReturn(tap($invoice)->update(['status' => Invoice::STATUS_PAID]));

        $action = new PayOrderAction(
            $this->createInvoiceActionMock,
            $this->payInvoiceActionMock
        );
        $order = $action->execute($order, $card);

        $this->assertEquals(Order::STATUS_PAID, $order->status);
        $this->assertEquals($invoice->id, $order->fresh()->invoice_id);
        Notification::assertSentTo($user, OrderPaidNotification::class);
    }

    public function test_it_throws_exception_and_restores_products_stock_if_payment_fails()
    {
        $user = User
            ::factory()
            ->create();
        $order = Order
            ::factory()
            ->for($user)
            ->hasAttached(
                $product = Product
                    ::factory()
                    ->state(['price' => 100])
                    ->create(),
                ['quantity' => 1]
            )
            ->create();
        $card = [
            'number' => $this->faker->creditCardNumber,
            'cvv' => $this->faker->numerify('###'),
            'expiration' => $this->faker->creditCardExpirationDate,
        ];

        $this->createInvoiceActionMock
            ->expects('execute')
            ->once()
            ->andReturn(
                $invoice = Invoice
                    ::factory()
                    ->for($user)
                    ->state(['amount' => 100])
                    ->create()
            );
        $this->payInvoiceActionMock
            ->expects('execute')
            ->once()
            ->with($invoice, $card)
            ->andReturn(tap($invoice)->update(['status' => Invoice::STATUS_CANCELED]));

        $action = new PayOrderAction(
            $this->createInvoiceActionMock,
            $this->payInvoiceActionMock
        );

        $this->expectExceptionMessage('Payment error.');

        $this->assertEquals(0, $product->fresh()->stock);

        try {
            $action->execute($order, $card);
        } finally {
            $this->assertEquals(Order::STATUS_CANCELED, $order->fresh()->status);
            $this->assertEquals(1, $product->fresh()->stock);
        }
    }
}
