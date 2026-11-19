<?php

namespace App\Http\Controllers;

use App\Dashboard\Attention;
use App\Dashboard\Onboarding;
use App\Dashboard\SyncLine;
use App\Models\AccountTarget;
use App\Models\ConnectedAccount;
use App\Models\Content;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Home: a creator with nothing set up goes to the wizard, everyone else to the dashboard.
    public function home(Request $request)
    {
        return redirect()->route(Onboarding::complete((bool) $request->session()->get('skip_targets')) ? 'dashboard' : 'onboarding');
    }

    public function index(Request $request)
    {
        if (! Onboarding::complete((bool) $request->session()->get('skip_targets'))) {
            return redirect()->route('onboarding');
        }

        $lines = AccountTarget::with(['source', 'target'])->get()->groupBy('source_account_id')->map(
            fn ($group) => SyncLine::format($group->first()->source->platform, $group->first()->source->handle,
                $group->map(fn ($t) => ['platform' => $t->target->platform, 'handle' => $t->target->handle, 'mode' => $t->mode])->all())
        )->values();

        return view('dashboard.index', [
            'attention' => Attention::items(),
            'recent' => Content::whereHas('copies', fn ($c) => $c->where('is_origin', true))->latest('id')->limit(8)->get(),
            'lines' => $lines,
            'hasAccounts' => ConnectedAccount::exists(),
        ]);
    }

    public function onboarding(Request $request)
    {
        $skipped = (bool) $request->session()->get('skip_targets');

        return view('dashboard.onboarding', [
            'steps' => Onboarding::steps($skipped),
            'accounts' => ConnectedAccount::orderBy('id')->get(),
            'targets' => AccountTarget::with(['source', 'target'])->get(),
        ]);
    }

    public function skipTargets(Request $request)
    {
        $request->session()->put('skip_targets', true);

        return redirect()->route('onboarding');
    }
}
