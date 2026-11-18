<?php

namespace Tests\Feature;

use App\Collections\FeedPayload;
use App\Collections\Rss;
use App\Collections\TokenIssuer;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Content;
use App\Models\ContentCopy;
use App\Models\EmbedSetting;
use App\Models\Tenant;
use App\Tenancy\Provisioner;
use App\Tenancy\Tenancy;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// PostgreSQL only. Own throwaway creator (900080). Goes through the real public URLs.
class CollectionsTest extends TestCase
{
    private const ID = 900080;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL only.');
        }
        app(Provisioner::class)->provision(self::ID);
        app(Tenancy::class)->run(self::ID, function () {
            Collection::query()->delete();
            Content::withTrashed()->forceDelete();
        });
    }

    private function collection(string $visibility, string $domains = 'site.example.com'): array
    {
        return app(Tenancy::class)->run(self::ID, function () use ($visibility, $domains) {
            $c = Collection::create(['name' => 'Morning', 'slug' => 'morning', 'visibility' => $visibility]);
            EmbedSetting::create(['collection_id' => $c->id, 'allowed_domains' => $domains]);
            $post = Content::create(['title' => 'Water first', 'master_text' => 'Water <b>first</b>']);
            ContentCopy::create(['content_id' => $post->id, 'platform' => 'instagram', 'external_id' => 'p', 'source_text' => 'x', 'source_hash' => 'h',
                'match_hash' => 'h', 'is_origin' => true, 'permalink' => 'https://www.instagram.com/p/x/']);
            CollectionItem::create(['collection_id' => $c->id, 'content_id' => $post->id, 'position' => 1]);

            return [$c, app(TokenIssuer::class)->issue(self::ID, $c)->token];
        });
    }

    public function test_a_link_collection_is_served_by_token_with_frame_and_cors_rules(): void
    {
        [, $token] = $this->collection('link');

        $page = $this->get('/embed/'.$token)->assertOk()->assertSee('Water')->assertHeader('X-Robots-Tag', 'noindex');
        $this->assertSame('frame-ancestors https://site.example.com', $page->headers->get('Content-Security-Policy'));
        $this->assertStringNotContainsString('<b>first</b>', $page->getContent()); // text is escaped

        $this->getJson('/feed/'.$token.'/json', ['Origin' => 'https://site.example.com'])->assertOk()
            ->assertJsonPath('items.0.title', 'Water first')->assertHeader('Access-Control-Allow-Origin', 'https://site.example.com');
        $this->assertNull($this->getJson('/feed/'.$token.'/json', ['Origin' => 'https://evil.com'])->headers->get('Access-Control-Allow-Origin'));
        $this->get('/feed/'.$token.'/rss')->assertOk()->assertSee('<item>', false);
    }

    public function test_private_revoked_wrong_and_unknown_tokens_get_nothing(): void
    {
        [$c, $token] = $this->collection('private');
        $this->get('/embed/'.$token)->assertNotFound();

        app(Tenancy::class)->run(self::ID, fn () => $c->update(['visibility' => 'link']));
        $this->get('/embed/'.$token)->assertOk();

        $new = app(Tenancy::class)->run(self::ID, fn () => app(TokenIssuer::class)->issue(self::ID, $c)->token);
        $this->get('/embed/'.$token)->assertNotFound(); // the old link was revoked
        $this->get('/embed/'.$new)->assertOk();
        $this->get('/embed/'.self::ID.'.'.str_repeat('z', 40))->assertNotFound();
        $this->get('/embed/garbage')->assertNotFound();
    }

    public function test_public_collection_has_a_readable_address_and_link_collections_do_not(): void
    {
        $this->collection('public');
        $this->get('/c/'.self::ID.'/morning')->assertOk()->assertSee('Water');

        app(Tenancy::class)->run(self::ID, fn () => Collection::where('slug', 'morning')->update(['visibility' => 'link']));
        $this->get('/c/'.self::ID.'/morning')->assertNotFound();
    }

    public function test_a_creator_being_deleted_is_not_served(): void
    {
        [, $token] = $this->collection('link');
        Tenant::where('creator_id', self::ID)->update(['status' => 'pending_deletion']);

        try {
            $this->get('/embed/'.$token)->assertNotFound();
        } finally {
            Tenant::where('creator_id', self::ID)->update(['status' => 'active']);
        }
    }

    public function test_payload_and_rss_escape_text(): void
    {
        [$c] = $this->collection('link');
        $payload = app(Tenancy::class)->run(self::ID, fn () => FeedPayload::build($c));

        $this->assertStringContainsString('&lt;b&gt;first&lt;/b&gt;', Rss::render($payload, 'https://x.test/feed'));
    }
}
