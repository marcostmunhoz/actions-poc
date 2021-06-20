<?php

namespace Tests\Unit\Actions;

use App\Actions\CreateInvoiceAction;
use App\Contracts\Services\InvoiceInterface;
use App\Contracts\Services\PaymentGatewayInterface;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class CreateInvoiceActionTest extends TestCase
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

    public function test_it_creates_invoice()
    {
        $amount = $this->faker->numberBetween(100, 10000);
        $identifier = $this->faker->uuid;
        $user = User
            ::factory()
            ->create();

        $this->gatewayMock
            ->expects('createInvoice')
            ->once()
            ->with($amount)
            ->andReturn(new class($identifier, $amount) implements InvoiceInterface {
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
            });

        $action = new CreateInvoiceAction($this->gatewayMock);
        $invoice = $action->execute($user, $amount);

        $this->assertEquals($identifier, $invoice->provider_id);
        $this->assertEquals($amount, $invoice->amount);
        $this->assertEquals(Invoice::STATUS_PENDING, $invoice->status);
        $this->assertTrue($user->is($invoice->user));
    }
}
