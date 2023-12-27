<?php

namespace Database\Factories\Order;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order\Order>
 */
class OrderFactory extends Factory
{
    /**
     * @var null|int[] ユーザーIDリスト
     */
    private static ?array $userIds;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        if (! isset(self::$userIds)) {
            self::$userIds = user::pluck('id')->toArray();
        }

        return [
            'user_id' => $this->faker->randomElement(self::$userIds),
            'sub_total' => $this->faker->numberBetween(1000, 10000),
            'discount' => $this->faker->numberBetween(0, 500),
            'total' => fn ($attributes) => $attributes['sub_total'] - $attributes['discount'],
        ];
    }
}
