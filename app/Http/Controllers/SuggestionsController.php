<?php

namespace App\Http\Controllers;

use App\Ai\Prompts;
use App\Ai\StyleContext;
use App\Content\Targets;
use App\Content\VersionRecorder;
use App\Jobs\SuggestTextJob;
use App\Models\AiInstruction;
use App\Models\AiKey;
use App\Models\AiSuggestion;
use App\Models\Content;
use App\Models\ContentTargetText;
use App\Publishing\TargetText;
use App\Settings\AiProviders;
use Illuminate\Http\Request;

// AI is only ever a suggestion: asking creates a row and a queued job, applying is a separate click.
class SuggestionsController extends Controller
{
    public const DAILY_CAP = 30;

    public function store(Request $request, Content $content, string $target)
    {
        abort_unless($target === 'master' || Targets::valid($target), 404);
        $action = (string) $request->input('action');
        abort_unless(in_array($action, Prompts::ACTIONS, true), 422);

        $data = $request->validate(['instruction' => ['nullable', 'string', 'max:600'], 'saved_id' => ['nullable', 'integer'],
            'save_as' => ['nullable', 'string', 'max:60']]);
        $key = AiKey::usable();
        if (! $key) {
            return back()->with('error', 'Add an AI key first: Settings, AI keys.');
        }
        if (AiSuggestion::where('created_at', '>=', now()->startOfDay())->count() >= self::DAILY_CAP) {
            return back()->with('error', 'Daily limit of '.self::DAILY_CAP.' AI suggestions reached. It protects your key from running up costs. Try tomorrow.');
        }

        // The saved instruction (if picked) first, then what was typed for this one.
        $typed = trim((string) ($data['instruction'] ?? ''));
        $saved = ! empty($data['saved_id']) ? AiInstruction::find($data['saved_id'])?->text : null;
        $instruction = trim(implode("\n", array_filter([$saved, $typed]))) ?: null;
        if ($typed !== '' && ! empty($data['save_as'])) {
            AiInstruction::create(['name' => $data['save_as'], 'text' => $typed]);
        }

        // One open suggestion per tab: a new request replaces the old one.
        AiSuggestion::where('content_id', $content->id)->where('target', $target)->whereIn('status', ['pending', 'offered', 'failed'])->update(['status' => 'dismissed']);
        $suggestion = AiSuggestion::create(['content_id' => $content->id, 'target' => $target, 'action' => $action, 'provider' => $key->provider,
            'model' => $key->default_model ?: AiProviders::ALL[$key->provider]['model'],
            'instruction' => $instruction, 'use_style' => $request->boolean('style') && StyleContext::available()]);
        SuggestTextJob::dispatch($request->attributes->get('creator_id'), $suggestion->id)->afterResponse();

        return redirect(route('content.show', $content).'#t-'.$target);
    }

    public function show(AiSuggestion $suggestion)
    {
        return response()->json(['status' => $suggestion->status]);
    }

    public function apply(AiSuggestion $suggestion, VersionRecorder $versions)
    {
        abort_unless($suggestion->status === 'offered' && $suggestion->offered_text, 404);
        $content = Content::findOrFail($suggestion->content_id);
        $text = $suggestion->offered_text;
        $target = $suggestion->target;
        $limit = Targets::LIMITS[$target] ?? null;
        if ($limit && mb_strlen($text) > $limit) {
            return back()->with('error', 'The suggestion is '.mb_strlen($text).' characters; '.ucfirst($target).' allows '.$limit.'. Ask again with Shorten.');
        }

        // The text you had is written to History first, so applying never loses it.
        $before = $target === 'master' ? $content->master_text : TargetText::for($content->loadMissing('targetTexts'), $target);
        $versions->record($content, $before, 'Before the AI change', $target === 'master' ? null : $target);
        $note = $suggestion->action === 'shorten' ? 'Shortened with AI' : 'Rewritten with AI';
        if ($target === 'master') {
            $content->update(['master_text' => $text]);
        } else {
            ContentTargetText::updateOrCreate(['content_id' => $content->id, 'target' => $target], ['text' => $text]);
        }
        $versions->record($content, $text, $note, $target === 'master' ? null : $target);
        $suggestion->update(['status' => 'applied']);

        return redirect(route('content.show', $content).'#t-'.$target)->with('status', 'Applied. Your earlier text is in History.');
    }

    public function dismiss(AiSuggestion $suggestion)
    {
        $suggestion->update(['status' => 'dismissed']);

        return redirect(route('content.show', $suggestion->content_id).'#t-'.$suggestion->target);
    }
}
