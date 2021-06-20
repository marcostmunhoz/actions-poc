<?php

namespace Tests\Unit\Actions;

use App\Contracts\Services\InvoiceInterface;
use App\Contracts\Services\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseMigrations;
    use WithFaker;

    private User $user;

    /**
     * @var MockInterface|PaymentGatewayInterface
     */
    private $gatewayMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User
            ::factory()
            ->create();
        Order::factory()
            ->times(10)
            ->for($this->user)
            ->hasAttached(
                Product::factory()->state(['stock' => 1]),
                ['quantity' => 1]
            )
            ->create();

        $this->actingAs($this->user, 'api');

        $this->gatewayMock = Mockery::mock(PaymentGatewayInterface::class);
        $this->instance(PaymentGatewayInterface::class, $this->gatewayMock);
    }

    public function test_it_returns_only_user_orders()
    {
        Order::factory()
            ->times(10)
            ->hasAttached(
                Product::factory()->state(['stock' => 1]),
                ['quantity' => 1]
            )
            ->create();

        $response = $this
            ->getJson(route('api.orders.index'));

        $response->assertJsonCount(10);
    }

    public function test_it_shows_user_order()
    {
        $order = $this
            ->user
            ->orders
            ->random();

        $response = $this->getJson(route('api.orders.show', $order));

        $this->assertEquals($order->id, $response->json('id'));
    }

    public function test_it_creates_a_order_for_users()
    {
        $product = Product
            ::factory()
            ->create([
                'stock' => 100,
            ]);

        $response = $this->postJson(
            route('api.orders.store'),
            [
                'products' => [
                    [
                        'id' => $product->id,
                        'quantity' => 1,
                    ],
                ],
            ]
        );

        $response->assertCreated();
        $this->assertEquals($product->price, $response->json('total'));
    }

    public function test_it_returns_error_when_theres_no_stock_available()
    {
        $product = Product
            ::factory()
            ->create([
                'stock' => 0,
            ]);

        $response = $this->postJson(
            route('api.orders.store'),
            [
                'products' => [
                    [
                        'id' => $product->id,
                        'quantity' => 1,
                    ],
                ],
            ]
        );

        $response->assertStatus(400);
        $this->assertEquals('Insufficient stock.', $response->json('message'));
    }

    public function test_it_pays_order()
    {
        $order = $this
            ->user
            ->orders
            ->random();

        $this->gatewayMock
            ->expects('createInvoice')
            ->once()
            ->andReturn($this->getInvoice(
                $this->faker->uuid,
                $order->total
            ));
        $this->gatewayMock
            ->expects('payInvoice')
            ->once()
            ->andReturn(true);

        $response = $this->postJson(
            route('api.orders.pay', $order),
            [
                'card' => [
                    'number' => $this->faker->creditCardNumber,
                    'cvv' => $this->faker->numerify('###'),
                    'expiration' => $this->faker->dateTimeBetween('+1 year', '+10 years')->format('Y-m'),
                ],
            ]
        );

        $response->assertOk();
    }

    public function test_it_returns_error_if_payment_fails()
    {
        $order = $this
            ->user
            ->orders
            ->random();

        $this->gatewayMock
            ->expects('createInvoice')
            ->once()
            ->andReturn($this->getInvoice(
                $this->faker->uuid,
                $order->total
            ));
        $this->gatewayMock
            ->expects('payInvoice')
            ->once()
            ->andReturn(false);

        $response = $this->postJson(
            route('api.orders.pay', $order),
            [
                'card' => [
                    'number' => $this->faker->creditCardNumber,
                    'cvv' => $this->faker->numerify('###'),
                    'expiration' => $this->faker->dateTimeBetween('+1 year', '+10 years')->format('Y-m'),
                ],
            ]
        );

        $response->assertStatus(400);
        $this->assertEquals('Payment error.', $response->json('message'));
    }

    private function getInvoice(string $identifier, int $amount)
    {
        return new class($identifier, $amount) implements InvoiceInterface {
            private string $identifier;
            private int $amount;

            public function __construct(string $identifier, int $amount)
            {
                $this->identifier = $identifier;
                $this->amount = $amount;
            }

            public function getIdentifier(): string
            {
                return $this->identifier;
            }

            public function getAmount(): int
            {
                return $this->amount;
            }
        };
    }
}
