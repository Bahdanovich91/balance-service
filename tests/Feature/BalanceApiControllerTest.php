<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\UserBalance;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Log;
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

    public function test_get_error_if_user_not_exists(): void
    {
        $response = $this->getJson('/api/balance/1');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'User not found (ID: 1)',
            ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Log::spy();
    }
}
