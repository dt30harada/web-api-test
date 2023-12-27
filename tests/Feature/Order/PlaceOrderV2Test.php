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
 * 注文処理のHTTPテスト リファクタリング後
 */
final class PlaceOrderV2Test extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function 正常に注文を処理できる(): void
    {
        // arrange
        Mail::fake();
        $this->travelTo(new DateTime('2023-12-05'));
        $this->seed(EbookFormatSeeder::class);
        $user = User::factory()->create([
            'email' => 'a@a.test',
        ]);
        $ebooks = Ebook::factory()->count(2)->sequence(
            ['price' => 1000],
            ['price' => 2000],
        )->create();

        // act
        $response = $this->actingAs($user)->postJson('api/v2/orders', [
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
            'discount' => 150,
            'total' => 2850,
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

    /**
     * @test
     */
    public function 未認証の場合は401エラーを返却する(): void
    {
        // arrange
        Mail::fake();

        // act
        $response = $this->postJson('api/v2/orders', [
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
        $response = $this->actingAs($user)->postJson('api/v2/orders', [
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
        $response = $this->actingAs($user)->postJson('api/v2/orders', [
            'ebookIds' => [$ebook->id],
        ]);

        // assert
        $response->assertConflict();
        Mail::assertNotSent(OrderPlaced::class);
    }
}
