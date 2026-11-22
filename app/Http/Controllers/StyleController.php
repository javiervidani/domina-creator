<?php

namespace App\Http\Controllers;

use App\Jobs\StyleGuideJob;
use App\Models\AiInstruction;
use App\Models\AiKey;
use App\Models\Content;
use App\Models\Setting;
use Illuminate\Http\Request;

// "My style": which posts are the creator's voice, the style guide they own, and their saved instructions.
class StyleController extends Controller
{
    public const MIN_POSTS = 3; // fewer than this tells the AI too little; 10 to 30 is the advice shown on the page

    public function voice(Content $content)
    {
        $content->update(['my_voice' => ! $content->my_voice]);

        return back()->with('status', $content->my_voice ? 'Marked as my voice.' : 'No longer marked as my voice.');
    }

    public function bulkVoice(Request $request)
    {
        $ids = array_values(array_unique(array_map('intval', (array) $request->input('ids', []))));
        if (! $ids) {
            return back()->with('error', 'Select posts first.');
        }
        Content::whereIn('id', $ids)->update(['my_voice' => true]);

        return back()->with('status', 'Marked '.count($ids).' post'.(count($ids) > 1 ? 's' : '').' as my voice.');
    }

    public function draft(Request $request)
    {
        if (! AiKey::usable()) {
            return back()->with('error', 'Add an AI key first: Settings, AI keys.');
        }
        if (Content::where('my_voice', true)->count() < self::MIN_POSTS) {
            return back()->with('error', 'Mark at least '.self::MIN_POSTS.' of your posts as my voice first (10 to 30 works best).');
        }
        Setting::putText('style_guide_job', 'pending');
        StyleGuideJob::dispatch($request->attributes->get('creator_id'))->afterResponse();

        return redirect(route('settings').'#s-style')->with('status', 'Writing a draft from your posts. Reload in a moment.');
    }

    // The textarea shows the draft if there is one, so saving it makes the draft the guide. Empty clears the guide.
    public function save(Request $request)
    {
        $text = trim((string) $request->validate(['style_guide' => ['nullable', 'string', 'max:4000']])['style_guide']);
        Setting::putText('style_guide', $text === '' ? null : $text);
        Setting::putText('style_guide_draft', null);

        return redirect(route('settings').'#s-style')->with('status', $text === '' ? 'Style guide cleared.' : 'Style guide saved.');
    }

    public function discard()
    {
        Setting::putText('style_guide_draft', null);
        Setting::putText('style_guide_job', null);

        return redirect(route('settings').'#s-style')->with('status', 'Draft discarded.');
    }

    public function instructionStore(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:60'], 'text' => ['required', 'string', 'max:600']]);
        $row = AiInstruction::create($data + ['is_default' => $request->boolean('is_default')]);
        if ($row->is_default) {
            AiInstruction::where('id', '!=', $row->id)->update(['is_default' => false]);
        }

        return redirect(route('settings').'#s-style')->with('status', 'Instruction saved.');
    }

    public function instructionDefault(AiInstruction $instruction)
    {
        AiInstruction::query()->update(['is_default' => false]);
        $instruction->update(['is_default' => true]);

        return redirect(route('settings').'#s-style')->with('status', 'Default instruction set.');
    }

    public function instructionDestroy(AiInstruction $instruction)
    {
        $instruction->delete();

        return redirect(route('settings').'#s-style')->with('status', 'Instruction removed.');
    }
}
