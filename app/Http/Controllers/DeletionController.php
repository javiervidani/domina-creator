<?php

namespace App\Http\Controllers;

use App\Models\CreatorMember;
use App\Models\Tenant;
use App\Tenancy\CreatorDeletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DeletionController extends Controller
{
    // Inside the workspace (tenant middleware): the owner confirms with the password and the word DELETE.
    public function request(Request $request, CreatorDeletion $deletion)
    {
        $data = $request->validate(['password' => ['required'], 'confirm' => ['required', 'in:DELETE']]);
        if (! Hash::check($data['password'], $request->user()->password)) {
            return back()->withErrors(['password' => 'The password is wrong.']);
        }

        $deletion->request((int) $request->attributes->get('creator_id'), $request->user()->id);

        return redirect()->route('deletion.pending');
    }

    // Outside the workspace (auth only): the schema is locked, so this page cannot use anything inside it.
    public function pending(Request $request)
    {
        $tenant = $this->tenant($request);
        if (! $tenant || $tenant->status !== 'pending_deletion') {
            return redirect()->route('home');
        }

        return view('deletion.pending', ['purgeAt' => $tenant->purge_at]);
    }

    public function restore(Request $request, CreatorDeletion $deletion)
    {
        $tenant = $this->tenant($request);
        abort_unless($tenant && $tenant->status === 'pending_deletion', 404);

        $deletion->restore($tenant->creator_id);

        return redirect()->route('accounts')->with('status', 'Your history is back. Reconnect your accounts: their tokens were removed.');
    }

    private function tenant(Request $request): ?Tenant
    {
        $creatorId = CreatorMember::where('user_id', $request->user()->id)->value('creator_id');

        return $creatorId ? Tenant::where('creator_id', $creatorId)->first() : null;
    }
}
