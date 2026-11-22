<?php

namespace App\Http\Controllers;

use App\Export\CreatorExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// Settings > Data > Export. The creator's own data, behind the password because it holds everything they wrote.
class ExportController extends Controller
{
    public function download(Request $request, CreatorExport $export)
    {
        $data = $request->validate(['password' => ['required']]);
        if (! Hash::check($data['password'], $request->user()->password)) {
            return back()->withErrors(['export_password' => 'The password is wrong.']);
        }

        return response()->download($export->build(), 'domina-creator-export-'.now()->format('Y-m-d').'.zip')->deleteFileAfterSend();
    }
}
