<?php

namespace App\Tenancy;

use App\Models\CreatorMember;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Runs after `auth`. The whole request, including view rendering, happens inside the creator's schema, and the
// schema is left again before the session is saved, because sessions live in public.
class EnterTenancy
{
    public function __construct(private Tenancy $tenancy)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $creatorId = CreatorMember::where('user_id', $request->user()->id)->value('creator_id');
        $tenant = $creatorId ? Tenant::where('creator_id', $creatorId)->first() : null;

        // Locked for deletion: the schema cannot be entered, so go to the page that offers to restore it.
        if ($tenant && $tenant->status === 'pending_deletion') {
            return redirect()->route('deletion.pending');
        }
        abort_unless($tenant && $tenant->status === 'active', 403, 'No active workspace.');

        $request->attributes->set('creator_id', (int) $creatorId);

        return $this->tenancy->run((int) $creatorId, fn () => $next($request));
    }
}
