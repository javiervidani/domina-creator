<?php

namespace App\Http\Controllers;

use App\Collections\TokenIssuer;
use App\Models\Collection;
use App\Models\EmbedSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CollectionsController extends Controller
{
    public function index()
    {
        return view('collections.index', ['collections' => Collection::withCount('items')->latest('id')->get()]);
    }

    public function store(Request $request, TokenIssuer $tokens)
    {
        $name = $request->validate(['name' => ['required', 'string', 'max:120']])['name'];
        $collection = $this->make($name, (int) $request->attributes->get('creator_id'), $tokens);

        return redirect()->route('collections.show', $collection)->with('status', 'Collection created. It is private until you change that.');
    }

    public function show(Request $request, Collection $collection)
    {
        $collection->load(['items.content', 'embed']);
        $token = $collection->tokens()->whereNull('revoked_at')->latest('id')->first();

        return view('collections.show', [
            'collection' => $collection,
            'embed' => $collection->embed,
            'token' => $token?->token,
            'creatorId' => (int) $request->attributes->get('creator_id'),
        ]);
    }

    public function update(Request $request, Collection $collection)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cover_url' => ['nullable', 'url', 'starts_with:https://', 'max:2000'],
            'visibility' => ['required', 'in:'.implode(',', array_keys(Collection::VISIBILITY))],
        ]);
        $collection->update($data);

        return back()->with('status', 'Saved.');
    }

    public function destroy(Collection $collection)
    {
        $collection->delete(); // items and tokens go with it; the contents stay in the Library

        return redirect()->route('collections')->with('status', 'Collection deleted. Its embed code and feeds stop working.');
    }

    // Shared with "Add to collection" in the Library, which can start a new collection.
    public function make(string $name, int $creatorId, TokenIssuer $tokens): Collection
    {
        $base = Str::slug($name) ?: 'collection';
        $slug = $base;
        for ($n = 2; Collection::where('slug', $slug)->exists(); $n++) {
            $slug = $base.'-'.$n;
        }

        $collection = Collection::create(['name' => $name, 'slug' => $slug]);
        EmbedSetting::create(['collection_id' => $collection->id]);
        $tokens->issue($creatorId, $collection);

        return $collection;
    }
}
