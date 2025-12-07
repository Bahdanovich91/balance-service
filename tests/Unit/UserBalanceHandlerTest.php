<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Dto\Request\DepositDto;
use App\Dto\Request\TransferDto;
use App\Dto\Request\WithdrawDto;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\UserNotFoundException;
use App\Handler\Balance\DepositHandler;
use App\Handler\Balance\TransferHandler;
use App\Handler\Balance\WithdrawHandler;
use App\Models\UserBalance;
use App\Repositories\UserBalanceRepository;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class UserBalanceHandlerTest extends TestCase
{
    use DatabaseMigrations;

    private DepositHandler $depositHandler;

    private WithdrawHandler $withdrawHandler;

    private TransferHandler $transferHandler;

    public function test_deposit_creates_new_user_balance(): void
    {
        $result = ($this->depositHandler)(new DepositDto(1, 500.00, 'Test deposit'));

        $this->assertEquals(500.00, $result->newBalance);
        $this->assertDatabaseHas('user_balance', [
            'user_id' => 1,
            'amount' => 500.00,
        ]);
    }

    public function test_deposit_updates_existing_balance(): void
    {
        UserBalance::create(['user_id' => 1, 'amount' => 200.00]);

        $result = ($this->depositHandler)(new DepositDto(1, 300.00, 'Additional deposit'));

        $this->assertEquals(500.00, $result->newBalance);
        $this->assertDatabaseHas('user_balance', [
            'user_id' => 1,
            'amount' => 500.00,
        ]);
    }

    public function test_withdraw_successfully(): void
    {
        UserBalance::create(['user_id' => 1, 'amount' => 1000.00]);

        $result = ($this->withdrawHandler)(new WithdrawDto(1, 200.00, 'Test withdrawal'));

        $this->assertEquals(800.00, $result->newBalance);
        $this->assertDatabaseHas('user_balance', [
            'user_id' => 1,
            'amount' => 800.00,
        ]);
    }

    public function test_withdraw_throws_insufficient_funds_exception(): void
    {
        UserBalance::create(['user_id' => 1, 'amount' => 100.00]);

        $this->expectException(InsufficientFundsException::class);
        ($this->withdrawHandler)(new WithdrawDto(1, 200.00, 'Attempt to withdraw more than available'));
    }

    public function test_transfer_successfully(): void
    {
        UserBalance::create(['user_id' => 1, 'amount' => 1000.00]);
        UserBalance::create(['user_id' => 2, 'amount' => 500.00]);

        $result = ($this->transferHandler)(new TransferDto(1, 2, 150, 'Test transfer'));

        $this->assertEquals(850.00, $result->fromUserBalance);
        $this->assertEquals(650.00, $result->toUserBalance);

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
        ($this->transferHandler)(new TransferDto(1, 2, 200, 'Attempt to transfer more than available'));
    }

    public function test_get_balance_returns_correct_amount(): void
    {
        UserBalance::create(['user_id' => 1, 'amount' => 350.00]);

        $userBalanceRepository = $this->app->make(UserBalanceRepository::class);
        $balance = (float) $userBalanceRepository->findByUserId(1)->amount;

        $this->assertEquals(350.00, $balance);
    }

    public function test_get_exception_if_user_not_exists(): void
    {
        $this->expectException(UserNotFoundException::class);
        $userBalanceRepository = $this->app->make(UserBalanceRepository::class);
        $userBalanceRepository->findByUserId(1) ?? throw new UserNotFoundException(1);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $transactionService = $this->app->make(TransactionService::class);
        $userBalanceRepository = $this->app->make(UserBalanceRepository::class);

        $this->depositHandler = new DepositHandler($userBalanceRepository, $transactionService);
        $this->withdrawHandler = new WithdrawHandler($userBalanceRepository, $transactionService);
        $this->transferHandler = new TransferHandler($userBalanceRepository, $transactionService);
    }
}
