<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;

// The smallest editor: title and text only. Per-target tabs, history and AI are step 7. Where Combine and a row click land.
class ContentController extends Controller
{
    public function show(Content $content)
    {
        return view('content', ['content' => $content->load(['parts', 'copies', 'tags'])]);
    }

    // Only the master text changes. The imported originals (content_copies) are never touched.
    public function update(Request $request, Content $content)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:255'], 'master_text' => ['required', 'string']]);
        $content->update($data);

        return back()->with('status', 'Saved.');
    }
}
