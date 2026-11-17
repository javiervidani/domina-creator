<?php

namespace App\Http\Controllers;

use App\Models\AccountTarget;
use Illuminate\Http\Request;

class TargetsController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'source_account_id' => ['required', 'integer', 'different:target_account_id', 'exists:connected_accounts,id'],
            'target_account_id' => ['required', 'integer', 'exists:connected_accounts,id'],
        ]);
        // mode and what_to_send keep their defaults: review first, text only.
        AccountTarget::firstOrCreate($data);

        return back()->with('status', 'Target added. It starts in review mode.');
    }

    public function update(Request $request, AccountTarget $target)
    {
        $target->update($request->validate(['mode' => ['required', 'in:auto,review']]));

        return back()->with('status', 'Saved.');
    }

    public function destroy(AccountTarget $target)
    {
        $target->delete();

        return back()->with('status', 'Target removed.');
    }
}
