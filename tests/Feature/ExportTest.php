<?php

namespace Tests\Feature;

use App\Export\CreatorExport;
use App\Models\Content;
use App\Models\Creator;
use App\Models\CreatorMember;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\CreatorDeletion;
use App\Tenancy\Provisioner;
use App\Tenancy\Tenancy;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use ZipArchive;

// PostgreSQL only. Makes its own creator and removes it afterwards, like DeletionTest.
class ExportTest extends TestCase
{
    private Creator $creator;

    protected function setUp(): void
    {
        parent::setUp();
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL only.');
        }
        $user = User::create(['name' => 'Exp', 'email' => 'exp'.uniqid().'@example.test', 'password' => 'a-long-password']);
        $this->creator = Creator::create(['name' => 'Exp']);
        CreatorMember::create(['user_id' => $user->id, 'creator_id' => $this->creator->id]);
        app(Provisioner::class)->provision($this->creator->id);
    }

    protected function tearDown(): void
    {
        if (DB::getDriverName() === 'pgsql' && isset($this->creator)) {
            Tenant::where('creator_id', $this->creator->id)->update(['status' => 'pending_deletion', 'purge_at' => now()->subMinute()]);
            app(CreatorDeletion::class)->purgeDue();
        }
        parent::tearDown();
    }

    public function test_the_zip_holds_one_csv_per_table_with_hebrew_and_no_secrets(): void
    {
        $path = app(Tenancy::class)->run($this->creator->id, function () {
            Content::create(['title' => 'שלום', 'master_text' => 'טקסט, עם פסיק']);

            return app(CreatorExport::class)->build();
        });

        $zip = new ZipArchive;
        $zip->open($path);
        $names = array_map(fn ($i) => $zip->getNameIndex($i), range(0, $zip->numFiles - 1));

        $this->assertContains('contents.csv', $names);
        $this->assertContains('README.txt', $names);
        foreach (['account_tokens.csv', 'ai_keys.csv', 'feed_tokens.csv'] as $secret) {
            $this->assertNotContains($secret, $names);
        }
        $this->assertStringContainsString('שלום', $zip->getFromName('contents.csv'));
        $this->assertStringContainsString('"טקסט, עם פסיק"', $zip->getFromName('contents.csv'));
        $zip->close();
        @unlink($path);
    }
}
