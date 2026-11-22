<?php

namespace App\Http\Controllers;

use App\Media\ImageKeeper;
use App\Models\AiKey;
use App\Models\ContentCopy;
use App\Models\Setting;
use App\Settings\AiProviders;
use App\Settings\StyleData;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public const NOTIFY = [
        'notify_failed_publish' => 'A publish to another account failed',
        'notify_token_expiring' => 'An account token is expiring or needs reconnecting',
        'notify_source_changed' => 'The original of a post I edited changed',
    ];

    public function index(Request $request)
    {
        return view('settings.index', [
            'user' => $request->user(),
            'notify' => collect(self::NOTIFY)->map(fn ($label, $key) => ['label' => $label, 'on' => Setting::flag($key)]),
            'keys' => AiKey::orderBy('id')->get()->keyBy('provider'),
            'providers' => AiProviders::ALL,
            'style' => StyleData::forView(),
            'pictures' => ['on' => ImageKeeper::enabled(), 'bytes' => ImageKeeper::used(), 'count' => ContentCopy::whereNotNull('kept_path')->count()],
        ]);
    }

    // Choices only for now: nothing is sent yet, the page says so.
    public function notifications(Request $request)
    {
        foreach (array_keys(self::NOTIFY) as $key) {
            Setting::put($key, $request->boolean($key));
        }

        return back()->with('status', 'Notification choices saved.');
    }
}
