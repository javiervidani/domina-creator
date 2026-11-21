<?php

namespace App\Http\Controllers;

use App\Media\ImageKeeper;
use App\Models\Setting;
use Illuminate\Http\Request;

// Settings > Pictures: the opt-in switch and "Delete kept pictures".
class PicturesController extends Controller
{
    public function update(Request $request)
    {
        Setting::put(ImageKeeper::SETTING, $request->boolean('keep_images'));

        return back()->with('status', $request->boolean('keep_images')
            ? 'Small copies of new pictures will be kept from the next sync.' : 'Switched off. Pictures already kept stay until you delete them.');
    }

    public function destroy(Request $request, ImageKeeper $keeper)
    {
        $keeper->forgetAll((int) $request->attributes->get('creator_id'));

        return back()->with('status', 'Kept pictures deleted. Posts show their picture by link again.');
    }
}
