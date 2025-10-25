<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\InsufficientFundsException;
use App\Models\UserBalance;
use App\Repositories\UserBalanceRepository;
use App\Services\TransactionService;
use App\Services\UserBalanceService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class UserBalanceServiceTest extends TestCase
{
    use DatabaseMigrations;

    private UserBalanceService $userBalanceService;

    public function test_deposit_creates_new_user_balance(): void
    {
        $result = $this->userBalanceService->deposit([
            'user_id' => 1,
            'amount' => 500.00,
            'comment' => 'Test deposit',
        ]);

        $this->assertArrayHasKey('transaction', $result);
        $this->assertArrayHasKey('new_balance', $result);
        $this->assertEquals(500.00, $result['new_balance']);

        $this->assertDatabaseHas('user_balance', [
            'user_id' => 1,
            'amount' => 500.00,
        ]);
    }

    public function test_deposit_updates_existing_balance(): void
    {
        UserBalance::create(['user_id' => 1, 'amount' => 200.00]);

        $result = $this->userBalanceService->deposit([
            'user_id' => 1,
            'amount' => 300.00,
            'comment' => 'Additional deposit',
        ]);

        $this->assertEquals(500.00, $result['new_balance']);
        $this->assertDatabaseHas('user_balance', [
            'user_id' => 1,
            'amount' => 500.00,
        ]);
    }

    public function test_withdraw_successfully(): void
    {
        UserBalance::create(['user_id' => 1, 'amount' => 1000.00]);

        $result = $this->userBalanceService->withdraw([
            'user_id' => 1,
            'amount' => 200.00,
            'comment' => 'Test withdrawal',
        ]);

        $this->assertEquals(800.00, $result['new_balance']);
        $this->assertDatabaseHas('user_balance', [
            'user_id' => 1,
            'amount' => 800.00,
        ]);
    }

    public function test_withdraw_throws_insufficient_funds_exception(): void
    {
        UserBalance::create(['user_id' => 1, 'amount' => 100.00]);

        $this->expectException(InsufficientFundsException::class);
        $this->userBalanceService->withdraw([
            'user_id' => 1,
            'amount' => 200.00,
            'comment' => 'Attempt to withdraw more than available',
        ]);
    }

    public function test_transfer_successfully(): void
    {
        UserBalance::create(['user_id' => 1, 'amount' => 1000.00]);
        UserBalance::create(['user_id' => 2, 'amount' => 500.00]);

        $result = $this->userBalanceService->transfer([
            'from_user_id' => 1,
            'to_user_id' => 2,
            'amount' => 150,
            'comment' => 'Test transfer',
        ]);

        $this->assertArrayHasKey('out_transaction', $result);
        $this->assertArrayHasKey('in_transaction', $result);
        $this->assertEquals(850.00, $result['from_user_balance']);
        $this->assertEquals(650.00, $result['to_user_balance']);

        $this->assertDatabaseHas('user_balance', [
            'user_id' => 1,
            'amount' => 850.00,
        ]);
        $this->assertDatabaseHas('user_balance', [
            'user_id' => 2,
            'amount' => 650.00,
        ]);
    }

    public function test_transfer_throws_insufficient_funds_exception(): void
    {
        UserBalance::create(['user_id' => 1, 'amount' => 100.00]);

        $this->expectException(InsufficientFundsException::class);
        $this->userBalanceService->transfer([
            'from_user_id' => 1,
            'to_user_id' => 2,
            'amount' => 200,
            'comment' => 'Attempt to transfer more than available',
        ]);
    }

    public function test_get_balance_returns_correct_amount(): void
    {
        UserBalance::create(['user_id' => 1, 'amount' => 350.00]);

        $balance = $this->userBalanceService->getBalance(1);

        $this->assertEquals(350.00, $balance);
    }

    public function test_get_balance_creates_user_if_not_exists(): void
    {
        $balance = $this->userBalanceService->getBalance(1);

        $this->assertEquals(0.0, $balance);
        $this->assertDatabaseHas('user_balance', [
            'user_id' => 1,
            'amount' => 0.0,
        ]);
    }

    public function test_transfer_with_array_data(): void
    {
        UserBalance::create(['user_id' => 1, 'amount' => 500.00]);
        $data = [
            'from_user_id' => 1,
            'to_user_id' => 2,
            'amount' => 100.00,
            'comment' => 'Array transfer test',
        ];

        $result = $this->userBalanceService->transfer($data);

        $this->assertEquals(400.00, $result['from_user_balance']);
        $this->assertEquals(100.00, $result['to_user_balance']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->userBalanceService = new UserBalanceService(
            $this->app->make(UserBalanceRepository::class),
            $this->app->make(TransactionService::class)
        );
    }
}
