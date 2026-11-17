<?php

namespace App\Http\Controllers;

use App\Accounts\AccountHealth;
use App\Jobs\SyncAccountJob;
use App\Models\AccountTarget;
use App\Models\ConnectedAccount;
use App\Models\SyncRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountsController extends Controller
{
    public function index()
    {
        $accounts = ConnectedAccount::with('token')->orderBy('id')->get();

        return view('accounts', [
            'accounts' => $accounts,
            'health' => $accounts->mapWithKeys(fn ($a) => [$a->id => AccountHealth::forAccount($a)]),
            'targets' => AccountTarget::with(['source', 'target'])->get(),
            'runs' => SyncRun::with('account')->latest('id')->limit(20)->get(),
        ]);
    }

    public function update(Request $request, ConnectedAccount $account)
    {
        $data = $request->validate([
            'import_enabled' => ['nullable', 'boolean'],
            'import_frequency' => ['required', 'in:fifteen_minutes,hourly,twicedaily,daily'],
        ]);
        $account->update(['import_enabled' => $request->boolean('import_enabled'), 'import_frequency' => $data['import_frequency']]);

        return back()->with('status', 'Saved.');
    }

    // Exactly one account holds the badge, so moving it is a swap inside one transaction.
    public function makeSource(ConnectedAccount $account)
    {
        DB::transaction(function () use ($account) {
            ConnectedAccount::where('is_source_of_truth', true)->update(['is_source_of_truth' => false]);
            $account->update(['is_source_of_truth' => true]);
        });

        return back()->with('status', '@'.$account->handle.' is now the source of truth.');
    }

    public function syncNow(Request $request, ConnectedAccount $account)
    {
        // After the response, outside the creator schema: the queue table is in public.
        SyncAccountJob::dispatch($request->attributes->get('creator_id'), $account->id)->afterResponse();

        return back()->with('status', 'Sync queued.');
    }

    public function destroy(ConnectedAccount $account)
    {
        $account->delete(); // the token row goes with it

        return back()->with('status', 'Account disconnected.');
    }
}
