<?php

namespace Tests\Feature;

use App\Http\Middleware\BetterBeWillie;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class BaseTestCase extends TestCase
{
    use RefreshDatabase;

    protected Account $account;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(BetterBeWillie::class);

        $this->account = Account::factory()->create();

        $this->user = User::factory()->create([
            'account_id' => $this->account->id,
            'is_admin' => false,
        ]);

        $this->actingAs($this->user);
    }

    /**
     * Create an admin user for the given account (defaults to $this->account).
     */
    protected function createAdminUser(?Account $account = null): User
    {
        return User::factory()->create([
            'account_id' => ($account ?? $this->account)->id,
            'is_admin' => true,
        ]);
    }

    /**
     * Create a secondary account for cross-account testing.
     */
    protected function createOtherAccount(): Account
    {
        return Account::factory()->create();
    }

    /**
     * Create a user for a specific account.
     */
    protected function createUserForAccount(Account $account, bool $isAdmin = false): User
    {
        return User::factory()->create([
            'account_id' => $account->id,
            'is_admin' => $isAdmin,
        ]);
    }
}
