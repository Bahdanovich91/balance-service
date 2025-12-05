<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\UserBalance;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class TransactionApiControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_deposit_creates_user_balance_and_transaction(): void
    {
        $response = $this->postJson('/api/deposit', [
            'user_id' => 1,
            'amount' => 500.00,
            'comment' => 'Пополнение через карту',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'transaction' => [
                    'id',
                    'to_user_id',
                    'type',
                    'amount',
                    'comment',
                ],
                'new_balance',
            ]);

        $this->assertDatabaseHas('user_balance', [
            'user_id' => 1,
            'amount' => 500.00,
        ]);

        $this->assertDatabaseHas('transactions', [
            'to_user_id' => 1,
            'type' => 'deposit',
            'amount' => 500.00,
            'comment' => 'Пополнение через карту',
        ]);
    }

    public function test_deposit_updates_existing_balance(): void
    {
        UserBalance::create([
            'user_id' => 1,
            'amount' => 100.00,
        ]);

        $response = $this->postJson('/api/deposit', [
            'user_id' => 1,
            'amount' => 200.00,
            'comment' => 'Дополнительное пополнение',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_balance', [
            'user_id' => 1,
            'amount' => 300.00,
        ]);
    }

    public function test_withdraw_successfully(): void
    {
        UserBalance::create([
            'user_id' => 1,
            'amount' => 1000.00,
        ]);

        $response = $this->postJson('/api/withdraw', [
            'user_id' => 1,
            'amount' => 200.00,
            'comment' => 'Покупка подписки',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'transaction',
                'new_balance',
            ]);

        $this->assertDatabaseHas('user_balance', [
            'user_id' => 1,
            'amount' => 800.00,
        ]);

        $this->assertDatabaseHas('transactions', [
            'to_user_id' => 1,
            'type' => 'withdraw',
            'amount' => 200.00,
        ]);
    }

    public function test_withdraw_insufficient_funds(): void
    {
        UserBalance::create([
            'user_id' => 1,
            'amount' => 100.00,
        ]);

        $response = $this->postJson('/api/withdraw', [
            'user_id' => 1,
            'amount' => 200.00,
            'comment' => 'Попытка списать больше чем есть',
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Insufficient funds',
            ]);
    }

    public function test_transfer_successfully(): void
    {
        UserBalance::create([
            'user_id' => 1,
            'amount' => 1000.00,
        ]);
        UserBalance::create([
            'user_id' => 2,
            'amount' => 500.00,
        ]);

        $response = $this->postJson('/api/transfer', [
            'from_user_id' => 1,
            'to_user_id' => 2,
            'amount' => 150.00,
            'comment' => 'Перевод другу',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'out_transaction',
                'in_transaction',
                'from_user_balance',
                'to_user_balance',
            ]);

        $this->assertDatabaseHas('user_balance', [
            'user_id' => 1,
            'amount' => 850.00,
        ]);

        $this->assertDatabaseHas('user_balance', [
            'user_id' => 2,
            'amount' => 650.00,
        ]);

        $this->assertDatabaseHas('transactions', [
            'from_user_id' => 1,
            'to_user_id' => 2,
            'type' => 'transfer_out',
            'amount' => 150.00,
        ]);

        $this->assertDatabaseHas('transactions', [
            'from_user_id' => 1,
            'to_user_id' => 2,
            'type' => 'transfer_in',
            'amount' => 150.00,
        ]);
    }

    public function test_transfer_insufficient_funds(): void
    {
        UserBalance::create([
            'user_id' => 1,
            'amount' => 100.00,
        ]);

        $response = $this->postJson('/api/transfer', [
            'from_user_id' => 1,
            'to_user_id' => 2,
            'amount' => 200.00,
            'comment' => 'Попытка перевести больше чем есть',
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Insufficient funds',
            ]);
    }

    public function test_validation_errors(): void
    {
        $response = $this->postJson('/api/deposit', [
            'user_id' => 'invalid',
            'amount' => -100,
            'comment' => str_repeat('a', 300),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'amount', 'comment']);
    }

    public function test_transfer_same_user_validation(): void
    {
        $response = $this->postJson('/api/transfer', [
            'from_user_id' => 1,
            'to_user_id' => 1,
            'amount' => 100.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['from_user_id']);
    }

    //TODO Добавить мок для KafkaService
    protected function setUp(): void
    {
        parent::setUp();

        Log::spy();
    }
}
