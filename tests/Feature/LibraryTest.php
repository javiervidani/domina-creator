<?php

namespace Tests\Feature;

use App\Library\Combiner;
use App\Library\LibraryQuery;
use App\Models\ConnectedAccount;
use App\Models\Content;
use App\Models\ContentCopy;
use App\Models\Tag;
use App\Tenancy\Provisioner;
use App\Tenancy\Tenancy;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// PostgreSQL only. Own throwaway creator (900030), emptied before each test.
class LibraryTest extends TestCase
{
    private const ID = 900030;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL only.');
        }
        app(Provisioner::class)->provision(self::ID);
        app(Tenancy::class)->run(self::ID, function () {
            Content::withTrashed()->forceDelete(); // copies, parts and tag links go with it
            Tag::query()->delete();
            ConnectedAccount::query()->delete();
        });
    }

    private function inCreator(callable $fn): mixed
    {
        return app(Tenancy::class)->run(self::ID, $fn);
    }

    private function seed(): array
    {
        $a = ConnectedAccount::create(['platform' => 'instagram', 'external_id' => 'a', 'handle' => 'a']);
        $b = ConnectedAccount::create(['platform' => 'youtube', 'external_id' => 'b', 'handle' => 'b']);
        $make = function (ConnectedAccount $acc, string $title, string $text, array $extra = []) {
            $c = Content::create(['title' => $title, 'master_text' => $text] + $extra);
            ContentCopy::create(['content_id' => $c->id, 'connected_account_id' => $acc->id, 'platform' => $acc->platform,
                'external_id' => $title, 'source_text' => $text, 'source_hash' => sha1($text), 'match_hash' => sha1($text), 'is_origin' => true]);

            return $c;
        };

        return [$a, $b, $make($a, 'Water', 'Water first'), $make($b, 'Light', 'Ten minutes of light', ['source_changed' => true]),
            $make($a, 'Draft one', 'Notes', ['status' => 'draft'])];
    }

    public function test_filters_narrow_the_list(): void
    {
        $this->inCreator(function () {
            [$a, $b] = $this->seed();

            $this->assertSame(3, LibraryQuery::build([])->count());
            $this->assertSame(1, LibraryQuery::build(['q' => 'light'])->count());
            $this->assertSame(2, LibraryQuery::build(['account' => $a->id])->count());
            $this->assertSame(1, LibraryQuery::build(['status' => 'draft'])->count());
            $this->assertSame(1, LibraryQuery::build(['changed' => 1])->count());
            $this->assertSame(1, LibraryQuery::build(['view' => 'review'])->count());
            $this->assertSame(0, LibraryQuery::build(['q' => '100%'])->count()); // % is searched literally
        });
    }

    public function test_combine_makes_a_draft_with_the_selected_items_as_ordered_parts(): void
    {
        $this->inCreator(function () {
            [, , $one, $two] = $this->seed();

            $combined = app(Combiner::class)->combine([$two->id, $one->id]);

            $this->assertSame('draft', $combined->status);
            $this->assertSame(['Ten minutes of light', 'Water first'], $combined->parts->pluck('text')->all());
            $this->assertSame("Ten minutes of light\n\nWater first", $combined->master_text);
            $this->assertSame('Water first', $one->fresh()->master_text); // the originals are untouched
        });
    }

    public function test_tagging_twice_does_not_duplicate(): void
    {
        $this->inCreator(function () {
            [, , $one] = $this->seed();
            $tag = Tag::create(['name' => 'morning']);

            $one->tags()->syncWithoutDetaching([$tag->id]);
            $one->tags()->syncWithoutDetaching([$tag->id]);

            $this->assertSame(1, $one->tags()->count());
        });
    }
}
