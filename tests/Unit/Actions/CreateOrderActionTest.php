<?php

namespace Tests\Unit\Actions;

use App\Actions\CheckProductStockAction;
use App\Actions\CreateOrderAction;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderCreatedNotification;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class CreateOrderActionTest extends TestCase
{
    use DatabaseMigrations;
    use WithFaker;

    /**
     * @var MockInterface|CheckProductStockAction
     */
    private $checkProductStockActionMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->checkProductStockActionMock = Mockery::mock(CheckProductStockAction::class);

        Notification::fake();
    }

    public function test_it_creates_a_order_with_given_products()
    {
        $user = User
            ::factory()
            ->create();
        $products = Product
            ::factory()
            ->times(5)
            ->state([
                'stock' => 1,
                'price' => 100,
            ])
            ->create();

        $this->checkProductStockActionMock
            ->expects('execute')
            ->times($products->count());

        $orderProducts = $products
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'quantity' => 1,
            ])
            ->all();
        $action = new CreateOrderAction($this->checkProductStockActionMock);
        $order = $action->execute($user, $orderProducts);

        $this->assertCount(5, $order->products);
        $this->assertEquals(500, $order->total);
        $this->assertEquals($user->id, $order->user_id);
        foreach ($products as $product) {
            $this->assertEquals(0, $product->fresh()->stock);
        }
        Notification::assertSentTo($user, OrderCreatedNotification::class);
    }
}
