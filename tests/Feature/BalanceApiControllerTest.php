<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\UserBalance;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class BalanceApiControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_get_balance(): void
    {
        UserBalance::create([
            'user_id' => 1,
            'amount' => 350.00,
        ]);

        $response = $this->getJson('/api/balance/1');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user_id' => 1,
                'balance' => 350.00,
            ]);
    }

    public function test_get_balance_creates_user_if_not_exists(): void
    {
        $response = $this->getJson('/api/balance/1');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user_id' => 1,
                'balance' => 0.0,
            ]);

        $this->assertDatabaseHas('user_balance', [
            'user_id' => 1,
            'amount' => 0.0,
        ]);
    }
}
