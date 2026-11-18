<?php

namespace App\Http\Controllers;

use App\Collections\AllowedDomains;
use App\Collections\FeedPayload;
use App\Collections\Rss;
use App\Collections\TokenIssuer;
use App\Models\Collection;
use App\Models\Tenant;
use App\Tenancy\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

// Public, read-only, no sign-in and no session (routes/public.php). A request names the creator, so the schema is
// entered here by hand, only for an active creator, and only a collection that is not private can be read.
class EmbedController extends Controller
{
    public function __construct(private Tenancy $tenancy, private TokenIssuer $tokens)
    {
    }

    public function byToken(Request $request, string $token, string $format = 'html')
    {
        $creatorId = TokenIssuer::creatorId($token);
        abort_unless($creatorId, 404);

        return $this->serve($request, $creatorId, $format, function () use ($token) {
            $collection = $this->tokens->find($token)?->collection;

            return $collection && $collection->visibility !== 'private' ? $collection : null;
        }, 'link');
    }

    public function bySlug(Request $request, int $creator, string $slug, string $format = 'html')
    {
        return $this->serve($request, $creator, $format, fn () => Collection::where('slug', $slug)->where('visibility', 'public')->first(), 'public');
    }

    private function serve(Request $request, int $creatorId, string $format, callable $find, string $mode): Response
    {
        abort_unless(Tenant::where('creator_id', $creatorId)->where('status', 'active')->exists(), 404);

        $payload = $this->tenancy->run($creatorId, fn () => ($c = $find()) ? FeedPayload::build($c) : null);
        abort_unless($payload, 404);

        $headers = ['Cache-Control' => 'public, max-age=60'];
        if ($mode === 'link') {
            $headers['X-Robots-Tag'] = 'noindex'; // a link-only collection is not for search engines
        }

        if ($format === 'json') {
            if ($origin = AllowedDomains::corsOrigin($payload['domains'], $request->headers->get('Origin'))) {
                $headers += ['Access-Control-Allow-Origin' => $origin, 'Vary' => 'Origin'];
            }

            return response()->json($payload, 200, $headers);
        }
        if ($format === 'rss') {
            return response(Rss::render($payload, $request->url()), 200, $headers + ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
        }

        return response(view('embed.page', ['p' => $payload])->render(), 200,
            $headers + ['Content-Security-Policy' => AllowedDomains::frameAncestors($payload['domains'])]);
    }
}
