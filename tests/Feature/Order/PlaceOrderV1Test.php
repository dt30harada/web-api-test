<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Mail\Order\OrderPlaced;
use App\Models\Ebook\Ebook;
use App\Models\Order\Order;
use App\Models\Order\OrderDetail;
use App\Models\User;
use Database\Seeders\EbookFormatSeeder;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * 注文処理のHTTPテスト リファクタリング前
 */
final class PlaceOrderV1Test extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     *
     * @dataProvider data_正常に注文を処理できる
     */
    public function 正常に注文を処理できる(string $today, bool $discount): void
    {
        // arrange
        Mail::fake();
        $this->travelTo(new DateTime($today));
        $this->seed(EbookFormatSeeder::class);
        $user = User::factory()->create([
            'email' => 'a@a.test',
        ]);
        $ebooks = Ebook::factory()->count(2)->sequence(
            ['price' => 1000],
            ['price' => 2000],
        )->create();

        // act
        $response = $this->actingAs($user)->postJson('api/v1/orders', [
            'ebookIds' => $ebooks->pluck('id')->toArray(),
        ]);

        // assert
        $response
            ->assertOk()
            ->assertJsonStructure([
                'orderId',
            ]);
        $this->assertDatabaseHas(Order::class, [
            'id' => $response->json('orderId'),
            'user_id' => $user->id,
            'sub_total' => 3000,
            'discount' => $discount ? 150 : 0,
            'total' => $discount ? 2850 : 3000,
        ]);
        $this->assertDatabaseHas(OrderDetail::class, [
            'order_id' => $response->json('orderId'),
            'price' => 1000,
        ]);
        $this->assertDatabaseHas(OrderDetail::class, [
            'order_id' => $response->json('orderId'),
            'price' => 2000,
        ]);
        Mail::assertSent(OrderPlaced::class, function ($mail) use ($response) {
            return $mail->hasTo('a@a.test')
                && $mail->hasSubject('注文完了のお知らせ')
                && $mail->order->id === $response->json('orderId');
        });
    }

    public static function data_正常に注文を処理できる(): array
    {
        return [
            '4日_割引なし' => [
                'date' => '2023-12-04',
                'discount' => false,
            ],
            '5日:割引あり' => [
                'date' => '2023-12-05',
                'discount' => true,
            ],
            '6日_割引なし' => [
                'date' => '2023-12-06',
                'discount' => false,
            ],
            '14日_割引なし' => [
                'date' => '2023-12-14',
                'discount' => false,
            ],
            '15日_割引あり' => [
                'date' => '2023-12-15',
                'discount' => true,
            ],
            '16日_割引なし' => [
                'date' => '2023-12-16',
                'discount' => false,
            ],
            '24日_割引なし' => [
                'date' => '2023-12-24',
                'discount' => false,
            ],
            '25日_割引あり' => [
                'date' => '2023-12-25',
                'discount' => true,
            ],
            '26日_割引なし' => [
                'date' => '2023-12-26',
                'discount' => false,
            ],
        ];
    }

    /**
     * @test
     */
    public function 未認証の場合は401エラーを返却する(): void
    {
        // arrange
        Mail::fake();

        // act
        $response = $this->postJson('api/v1/orders', [
            'ebookIds' => [1],
        ]);

        // assert
        $response->assertUnauthorized();
        Mail::assertNotSent(OrderPlaced::class);
    }

    /**
     * @test
     */
    public function リクエストパラメータの形式が不正な場合は422エラーを返却する(): void
    {
        // arrange
        Mail::fake();
        $user = User::factory()->create();

        // act
        $response = $this->actingAs($user)->postJson('api/v1/orders', [
            'ebookIds' => 'not an array',
        ]);

        // assert
        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'ebookIds' => [
                    'The ebook ids field must be an array.',
                ],
            ],
        ]);
        Mail::assertNotSent(OrderPlaced::class);
    }

    /**
     * @test
     */
    public function 過去に注文済みの電子書籍を注文した場合は409エラーを返却する(): void
    {
        // arrange
        Mail::fake();
        $this->seed(EbookFormatSeeder::class);
        $user = User::factory()->create();
        $ebook = Ebook::factory()->create();
        Order::factory()
            ->has(OrderDetail::factory()->state([
                'ebook_id' => $ebook->id,
            ]))
            ->create([
                'user_id' => $user->id,
            ]);

        // act
        $response = $this->actingAs($user)->postJson('api/v1/orders', [
            'ebookIds' => [$ebook->id],
        ]);

        // assert
        $response->assertConflict();
        Mail::assertNotSent(OrderPlaced::class);
    }
}
