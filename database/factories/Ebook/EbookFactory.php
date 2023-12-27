<?php

declare(strict_types=1);

namespace Database\Factories\Ebook;

use App\Models\Ebook\EbookFormat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ebook\Ebook>
 */
final class EbookFactory extends Factory
{
    /**
     * @var null|int[] 電子書籍形式のIDリスト
     */
    private static ?array $formatIds;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        if (! isset(self::$formatIds)) {
            self::$formatIds = EbookFormat::pluck('id')->toArray();
        }

        return [
            'author' => $this->faker->name,
            'title' => $this->faker->bothify('タイトル: ???-#####'),
            'publisher' => $this->faker->company,
            'price' => $this->faker->numberBetween(1000, 5000),
            'description' => $this->faker->realText,
            'isbn' => $this->faker->unique()->isbn13,
            'published_date' => $this->faker->date(),
            'format_id' => $this->faker->randomElement(self::$formatIds),
        ];
    }
}
