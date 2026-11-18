<?php

namespace Tests\Feature;

use App\Models\AiKey;
use App\Models\Setting;
use App\Tenancy\Provisioner;
use App\Tenancy\Tenancy;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// PostgreSQL only. Own throwaway creator (900070).
class SettingsTest extends TestCase
{
    private const ID = 900070;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL only.');
        }
        app(Provisioner::class)->provision(self::ID);
        app(Tenancy::class)->run(self::ID, function () {
            AiKey::query()->delete();
            Setting::query()->delete();
        });
    }

    public function test_a_key_is_stored_encrypted_masked_and_removable(): void
    {
        app(Tenancy::class)->run(self::ID, function () {
            $k = AiKey::create(['provider' => 'claude', 'api_key' => 'sk-ant-supersecret-9999', 'is_default' => true]);

            $this->assertStringNotContainsString('supersecret', DB::table('ai_keys')->value('api_key'));
            $this->assertSame('••••9999', $k->masked());
            $this->assertArrayNotHasKey('api_key', $k->toArray());

            $k->delete();
            $this->assertSame(0, AiKey::count());
        });
    }

    public function test_notification_choices_round_trip(): void
    {
        app(Tenancy::class)->run(self::ID, function () {
            Setting::put('notify_failed_publish', true);
            Setting::put('notify_source_changed', false);

            $this->assertTrue(Setting::flag('notify_failed_publish'));
            $this->assertFalse(Setting::flag('notify_source_changed'));
            $this->assertFalse(Setting::flag('never_set'));
        });
    }

    public function test_outside_leaves_the_schema_and_comes_back(): void
    {
        $t = app(Tenancy::class);
        $t->run(self::ID, function () use ($t) {
            $t->outside(function () use ($t) {
                $this->assertNull($t->current());
                $this->assertSame('public', DB::selectOne('show search_path')->search_path);
            });
            $this->assertSame(self::ID, $t->current());
        });
    }
}
