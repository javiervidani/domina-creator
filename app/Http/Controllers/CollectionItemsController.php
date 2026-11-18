<?php

namespace App\Http\Controllers;

use App\Collections\TokenIssuer;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Content;
use Illuminate\Http\Request;

class CollectionItemsController extends Controller
{
    // From the Library: the selected contents go to an existing collection, or to a new one.
    public function addFromLibrary(Request $request, TokenIssuer $tokens, CollectionsController $collections)
    {
        $ids = array_values(array_unique(array_map('intval', (array) $request->input('ids', []))));
        $name = trim((string) $request->input('new_name'));
        if (! $ids) {
            return back()->with('error', 'Select items first.');
        }

        $collection = $name !== ''
            ? $collections->make(mb_substr($name, 0, 120), (int) $request->attributes->get('creator_id'), $tokens)
            : Collection::find((int) $request->input('collection_id'));
        if (! $collection) {
            return back()->with('error', 'Choose a collection or type a new name.');
        }

        $position = (int) CollectionItem::where('collection_id', $collection->id)->max('position');
        $added = 0;
        foreach (Content::whereIn('id', $ids)->orderBy('id')->pluck('id') as $contentId) {
            if (! CollectionItem::where('collection_id', $collection->id)->where('content_id', $contentId)->exists()) {
                CollectionItem::create(['collection_id' => $collection->id, 'content_id' => $contentId, 'position' => ++$position]);
                $added++;
            }
        }

        return redirect()->route('collections.show', $collection)->with('status', $added.' item'.($added === 1 ? '' : 's').' added to '.$collection->name.'.');
    }

    public function move(Request $request, Collection $collection, CollectionItem $item)
    {
        abort_unless($item->collection_id === $collection->id, 404);
        $items = $collection->items()->get()->values();
        $i = $items->search(fn ($x) => $x->id === $item->id);
        $swap = $items->get($i + ($request->input('dir') === 'up' ? -1 : 1));

        if ($swap) {
            [$a, $b] = [$item->position, $swap->position];
            $item->update(['position' => $b]);
            $swap->update(['position' => $a]);
        }

        return back();
    }

    public function remove(Collection $collection, CollectionItem $item)
    {
        abort_unless($item->collection_id === $collection->id, 404);
        $item->delete();

        return back()->with('status', 'Removed from the collection. The content stays in the Library.');
    }
}
