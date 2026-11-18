<?php

namespace Tests\Feature;

use App\Import\ContentImporter;
use App\Models\ConnectedAccount;
use App\Models\Content;
use App\Models\ContentCopy;
use App\Tenancy\Provisioner;
use App\Tenancy\Tenancy;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// PostgreSQL only. Own throwaway creator (900020), emptied before each test.
class ImportTest extends TestCase
{
    private const ID = 900020;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL only.');
        }
        app(Provisioner::class)->provision(self::ID);
        app(Tenancy::class)->run(self::ID, function () {
            ContentCopy::query()->delete();
            Content::withTrashed()->forceDelete();
            ConnectedAccount::query()->delete();
        });
    }

    private function run_(callable $fn): mixed
    {
        return app(Tenancy::class)->run(self::ID, $fn);
    }

    private function account(string $platform, string $handle): ConnectedAccount
    {
        return ConnectedAccount::create(['platform' => $platform, 'external_id' => $handle, 'handle' => $handle]);
    }

    public function test_a_second_run_creates_no_duplicates(): void
    {
        $this->run_(function () {
            $a = $this->account('instagram', 'a');
            $item = ['id' => '1', 'caption' => 'Water first. Then light.', 'timestamp' => '2026-11-15T09:00:00+0000'];
            $importer = app(ContentImporter::class);

            $this->assertSame('created', $importer->import($a, $item));
            $this->assertSame('unchanged', $importer->import($a, $item));
            $this->assertSame(1, Content::count());
            $this->assertSame(1, ContentCopy::count());
        });
    }

    public function test_a_changed_original_flags_an_edited_text_and_never_overwrites_it(): void
    {
        $this->run_(function () {
            $a = $this->account('instagram', 'a');
            $importer = app(ContentImporter::class);
            $importer->import($a, ['id' => '1', 'caption' => 'Original text']);
            $content = Content::first();
            $content->update(['master_text' => 'My own edit']);

            $this->assertSame('changed', $importer->import($a, ['id' => '1', 'caption' => 'Original text, edited on Instagram']));

            $content->refresh();
            $this->assertSame('My own edit', $content->master_text);
            $this->assertTrue($content->source_changed);
        });
    }

    public function test_an_untouched_text_follows_the_original(): void
    {
        $this->run_(function () {
            $a = $this->account('instagram', 'a');
            $importer = app(ContentImporter::class);
            $importer->import($a, ['id' => '1', 'caption' => 'First wording']);
            $importer->import($a, ['id' => '1', 'caption' => 'Second wording']);

            $content = Content::first();
            $this->assertSame('Second wording', $content->master_text);
            $this->assertFalse($content->source_changed);
        });
    }

    public function test_the_same_text_on_another_account_links_to_the_existing_content(): void
    {
        $this->run_(function () {
            $ig = $this->account('instagram', 'a');
            $yt = $this->account('youtube', 'b');
            $importer = app(ContentImporter::class);

            $importer->import($ig, ['id' => '1', 'caption' => 'Three things before 8am']);
            $this->assertSame('linked', $importer->import($yt, ['id' => 'v9', 'caption' => 'three things  before 8am']));

            $this->assertSame(1, Content::count());
            $this->assertSame(2, ContentCopy::count());
        });
    }

    public function test_posts_without_a_caption_are_skipped(): void
    {
        $this->run_(function () {
            $this->assertSame('skipped', app(ContentImporter::class)->import($this->account('instagram', 'a'), ['id' => '1', 'caption' => '']));
            $this->assertSame(0, Content::count());
        });
    }
}
