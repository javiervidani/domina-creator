<?php

namespace App\Tenancy;

use App\Models\AccountToken;
use App\Models\ConnectedAccount;
use App\Models\CreatorMember;
use App\Models\DeletionRequest;
use App\Models\FeedToken;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

// Deleting a creator's history with 60 days to change their mind (DATABASE.md, "Deleting a creator's history").
// Request: disconnect, then lock. Restore: unlock. Purge: after day 60, drop the schema and the role.
class CreatorDeletion
{
    public const DAYS = 60;

    public function __construct(private Tenancy $tenancy)
    {
    }

    public function request(int $creatorId, int $userId): DeletionRequest
    {
        // While we can still enter the schema: forget the tokens and stop the public links. Nothing is dropped.
        $this->tenancy->run($creatorId, function () {
            AccountToken::query()->delete();
            ConnectedAccount::query()->update(['status' => 'reconnect', 'last_error' => 'Disconnected when the history was deleted.']);
            FeedToken::whereNull('revoked_at')->update(['revoked_at' => now()]);
        });

        // Now outside the schema. The status goes first because the SQL function checks it before it locks.
        return DB::transaction(function () use ($creatorId, $userId) {
            Tenant::where('creator_id', $creatorId)->update(['status' => 'pending_deletion', 'purge_at' => now()->addDays(self::DAYS)]);
            DB::select('select public.lock_creator(?)', [$creatorId]);

            return DeletionRequest::create(['creator_id' => $creatorId, 'requested_by' => $userId, 'purge_at' => now()->addDays(self::DAYS)]);
        });
    }

    public function restore(int $creatorId): void
    {
        DB::transaction(function () use ($creatorId) {
            DB::select('select public.unlock_creator(?)', [$creatorId]);
            Tenant::where('creator_id', $creatorId)->update(['status' => 'active', 'purge_at' => null]);
            DeletionRequest::where('creator_id', $creatorId)->whereNull('restored_at')->whereNull('purged_at')->update(['restored_at' => now()]);
        });
    }

    /** @return int how many creators were purged */
    public function purgeDue(): int
    {
        $purged = 0;

        foreach (Tenant::where('status', 'pending_deletion')->where('purge_at', '<=', now())->pluck('creator_id') as $creatorId) {
            try {
                DB::transaction(fn () => $this->purge($creatorId));
                $purged++;
            } catch (Throwable $e) {
                // One stuck creator must not stop the others; it is tried again tomorrow.
                Log::error("Purge of creator {$creatorId} failed: ".$e->getMessage());
            }
        }

        return $purged;
    }

    private function purge(int $creatorId): void
    {
        $userIds = CreatorMember::where('creator_id', $creatorId)->pluck('user_id');

        DB::select('select public.purge_creator(?)', [$creatorId]); // checks that day 60 has passed

        // Central rows go; deletion_requests stays as the minimal record (ids and dates, no name or email).
        foreach ($userIds as $userId) {
            if (CreatorMember::where('user_id', $userId)->where('creator_id', '!=', $creatorId)->doesntExist()) {
                DB::table('public.sessions')->where('user_id', $userId)->delete();
                User::whereKey($userId)->delete();
            }
        }
        DB::table('public.creators')->where('id', $creatorId)->delete(); // members and the tenant row follow
        DeletionRequest::where('creator_id', $creatorId)->whereNull('restored_at')->whereNull('purged_at')->update(['purged_at' => now()]); // not the request that was restored earlier
    }
}
