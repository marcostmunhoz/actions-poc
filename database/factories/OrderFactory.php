<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'total' => $this->faker->numberBetween(100, 10000),
            'status' => Order::STATUS_PENDING,
            'user_id' => User::factory(),
            'invoice_id' => Invoice::factory(),
        ];
    }
}
