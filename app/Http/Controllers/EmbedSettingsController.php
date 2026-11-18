<?php

namespace App\Http\Controllers;

use App\Collections\AllowedDomains;
use App\Collections\TokenIssuer;
use App\Models\Collection;
use App\Models\EmbedSetting;
use Illuminate\Http\Request;

class EmbedSettingsController extends Controller
{
    public function update(Request $request, Collection $collection)
    {
        $data = $request->validate([
            'layout' => ['required', 'in:list,grid'],
            'accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'allowed_domains' => ['nullable', 'string', 'max:2000'],
        ]);
        $domains = AllowedDomains::parse($data['allowed_domains'] ?? '');

        EmbedSetting::updateOrCreate(['collection_id' => $collection->id], [
            'layout' => $data['layout'], 'accent' => $data['accent'], 'show_media' => $request->boolean('show_media'),
            'allowed_domains' => implode("\n", $domains),
        ]);

        return back()->with('status', 'Embed settings saved. '.($domains ? count($domains).' allowed site(s).' : 'No site is allowed to show it yet.'));
    }

    // A new link. Everything that used the old one (embed code, RSS, JSON) stops working immediately.
    public function regenerate(Request $request, Collection $collection, TokenIssuer $tokens)
    {
        $tokens->issue((int) $request->attributes->get('creator_id'), $collection);

        return back()->with('status', 'New link created. The old link no longer works: update your website with the new embed code.');
    }
}
