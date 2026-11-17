<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

// Empty for now: it only proves the request is inside the creator's own schema. Step 6 fills it.
class LibraryController extends Controller
{
    public function __invoke()
    {
        return view('library', ['schema' => DB::selectOne('select current_schema() as s')->s]);
    }
}
