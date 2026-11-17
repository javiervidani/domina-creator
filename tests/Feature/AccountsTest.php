<?php

namespace Tests\Feature;

use App\Jobs\RefreshTokenJob;
use App\Models\AccountToken;
use App\Models\ConnectedAccount;
use App\Tenancy\Provisioner;
use App\Tenancy\Tenancy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

// PostgreSQL only. Works in its own throwaway creator (id 900010) and clears the account rows each time.
class AccountsTest extends TestCase
{
    private const ID = 900010;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL only.');
        }
        app(Provisioner::class)->provision(self::ID);
        app(Tenancy::class)->run(self::ID, fn () => ConnectedAccount::query()->delete());
    }

    private function account(string $handle): ConnectedAccount
    {
        return ConnectedAccount::create(['platform' => 'instagram', 'external_id' => $handle, 'handle' => $handle]);
    }

    public function test_token_is_stored_encrypted(): void
    {
        app(Tenancy::class)->run(self::ID, function () {
            $a = $this->account('enc');
            $a->token()->create(['access_token' => 'plain-secret', 'expires_at' => now()->addDays(30)]);

            $raw = DB::table('account_tokens')->value('access_token');
            $this->assertStringNotContainsString('plain-secret', $raw);
            $this->assertSame('plain-secret', AccountToken::first()->access_token);
        });
    }

    public function test_refresh_job_extends_the_token(): void
    {
        Http::fake(['graph.instagram.com/*' => Http::response(['access_token' => 'fresh', 'expires_in' => 5184000])]);
        $id = app(Tenancy::class)->run(self::ID, function () {
            $a = $this->account('ref');
            $a->token()->create(['access_token' => 'old', 'expires_at' => now()->addDays(3)]);

            return $a->id;
        });

        (new RefreshTokenJob(self::ID, $id))->handle(app(Tenancy::class));

        app(Tenancy::class)->run(self::ID, function () use ($id) {
            $token = AccountToken::where('connected_account_id', $id)->first();
            $this->assertSame('fresh', $token->access_token);
            $this->assertTrue($token->expires_at->isAfter(now()->addDays(50)));
        });
    }

    public function test_dead_token_marks_the_account_reconnect(): void
    {
        Http::fake(['*' => Http::response(['error' => ['code' => 190, 'message' => 'Expired']], 400)]);
        $id = app(Tenancy::class)->run(self::ID, function () {
            $a = $this->account('dead');
            $a->token()->create(['access_token' => 'old', 'expires_at' => now()->addDays(3)]);

            return $a->id;
        });

        (new RefreshTokenJob(self::ID, $id))->handle(app(Tenancy::class));

        app(Tenancy::class)->run(self::ID, function () use ($id) {
            $this->assertSame('reconnect', ConnectedAccount::find($id)->status);
        });
    }
}
