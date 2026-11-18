<?php

namespace App\Http\Controllers;

use App\Library\LibraryQuery;
use App\Models\Collection;
use App\Models\ConnectedAccount;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->only(['q', 'account', 'status', 'changed', 'view']);

        return view('library', [
            'items' => LibraryQuery::build($filters)->paginate(25)->withQueryString(),
            'accounts' => ConnectedAccount::orderBy('id')->get(['id', 'platform', 'handle']),
            'collections' => Collection::orderBy('name')->get(['id', 'name']),
            'f' => $filters,
        ]);
    }
}
