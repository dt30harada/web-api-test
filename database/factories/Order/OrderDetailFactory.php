<?php

namespace Database\Factories\Order;

use App\Models\Ebook\Ebook;
use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order\OrderDetail>
 */
class OrderDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'ebook_id' => Ebook::factory(),
            'ebook_title' => fn (array $attributes) => Ebook::find($attributes['ebook_id'])->title,
            'price' => fn (array $attributes) => Ebook::find($attributes['ebook_id'])->price,
            'discount' => $this->faker->numberBetween(100, 500),
        ];
    }
}
