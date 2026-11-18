<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Content;
use App\Tenancy\CreatorDeletion;
use Illuminate\Http\Request;

// Single items. Deleting a post or a collection only sets deleted_at; the daily purge removes them after 60 days.
class RecycleBinController extends Controller
{
    private const TYPES = ['content' => Content::class, 'collection' => Collection::class];

    public function index()
    {
        return view('recycle.index', [
            'contents' => Content::onlyTrashed()->latest('deleted_at')->get(),
            'collections' => Collection::onlyTrashed()->latest('deleted_at')->get(),
            'days' => CreatorDeletion::DAYS,
        ]);
    }

    public function destroyContent(Content $content)
    {
        $content->delete();

        return redirect()->route('library')->with('status', 'Moved to the Recycle bin for '.CreatorDeletion::DAYS.' days.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = array_values(array_unique(array_map('intval', (array) $request->input('ids', []))));
        $count = $ids ? Content::whereIn('id', $ids)->delete() : 0;

        return back()->with($count ? 'status' : 'error', $count ? $count.' moved to the Recycle bin for '.CreatorDeletion::DAYS.' days.' : 'Select items first.');
    }

    public function restore(string $type, int $id)
    {
        abort_unless(isset(self::TYPES[$type]), 404);
        self::TYPES[$type]::onlyTrashed()->findOrFail($id)->restore();

        return back()->with('status', 'Restored.');
    }

    // Gone for good. An imported post that is still on its network can come back on a later sync as a new item.
    public function forget(string $type, int $id)
    {
        abort_unless(isset(self::TYPES[$type]), 404);
        self::TYPES[$type]::onlyTrashed()->findOrFail($id)->forceDelete();

        return back()->with('status', 'Deleted for good.');
    }
}
