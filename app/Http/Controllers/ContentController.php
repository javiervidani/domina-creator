<?php

namespace App\Http\Controllers;

use App\Models\Content;

// Read-only for now. The editor itself is step 7; this page is where Combine and a row click land.
class ContentController extends Controller
{
    public function show(Content $content)
    {
        return view('content', ['content' => $content->load(['parts', 'copies', 'tags'])]);
    }
}
