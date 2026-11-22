{{-- AI is only a suggestion: it appears next to your text and nothing changes until you press Apply. --}}
@php($s = $suggestions[$target] ?? null)
<form method="post" action="{{ route('ai.suggest', [$content, $target]) }}" class="mt-2">@csrf
    <div class="d-flex gap-2 flex-wrap">
        @foreach (['rewrite' => 'Rewrite with AI', 'shorten' => 'Shorten with AI'] as $action => $label)
            <button name="action" value="{{ $action }}" class="btn btn-sm btn-outline-primary rounded-pill" @disabled(! $aiReady || ($s && $s->status === 'pending'))>{{ $label }}</button>
        @endforeach
    </div>
    @if ($aiReady)
    <details class="mt-2"><summary class="small"><span class="pill">Instruction and my style</span></summary>
        <div class="mt-2">
            <textarea name="instruction" rows="2" maxlength="600" dir="auto" class="form-control form-control-sm" placeholder="Your instruction for this edit (optional), for example: warmer, short sentences, no exclamation marks"></textarea>
            @if ($instructions->isNotEmpty())
                <select name="saved_id" class="form-select form-select-sm mt-2" dir="auto"><option value="">No saved instruction</option>
                    @foreach ($instructions as $i)<option value="{{ $i->id }}" @selected($i->is_default)>{{ $i->name }}</option>@endforeach</select>
            @endif
            <input name="save_as" maxlength="60" dir="auto" class="form-control form-control-sm mt-2" placeholder="Save the typed instruction as… (optional name)">
            <div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="style" value="1" id="style-{{ $target }}" @checked($styleAvailable) @disabled(! $styleAvailable)>
                <label class="form-check-label small" for="style-{{ $target }}">In my style
                    @unless ($styleAvailable)<span class="text-muted">(mark some of your posts as my voice first: Settings, My style)</span>@endunless</label></div>
        </div>
    </details>
    @endif
</form>
@unless ($aiReady)<div class="small text-muted mt-1">Add an AI key in <a href="{{ route('settings') }}">Settings</a> to use this.</div>@endunless

@if ($s && $s->status === 'pending')
    <div class="banner mt-3" id="ai-wait-{{ $target }}"><div>Asking the AI… This page updates by itself.
        <div class="small text-muted">If it takes more than a minute, the queue worker may not be running.</div></div></div>
    <script>
        (function () {
            var t = setInterval(function () {
                fetch('{{ route('ai.suggestion', $s) }}', { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (d) { if (d.status !== 'pending') { clearInterval(t); location.reload(); } })
                    .catch(function () {});
            }, 2500);
        })();
    </script>
@elseif ($s && $s->status === 'failed')
    <div class="banner mt-3"><div><b>The AI could not do it.</b> <span class="text-danger">{{ $s->error }}</span>
        <form method="post" action="{{ route('ai.dismiss', $s) }}" class="mt-2">@csrf<button class="btn btn-sm btn-outline-secondary rounded-pill">Close</button></form></div></div>
@elseif ($s && $s->status === 'offered')
    @php($current = $target === 'master' ? $content->master_text : \App\Publishing\TargetText::for($content, $target))
    @php($diff = \App\Content\TextDiff::sides($current, $s->offered_text))
    @php($limit = \App\Content\Targets::LIMITS[$target] ?? null)
    @php($over = $limit && mb_strlen($s->offered_text) > $limit)
    <div class="card mt-3"><div class="card-body">
        <div class="d-flex justify-content-between flex-wrap gap-1 mb-2">
            <strong>{{ $s->action === 'shorten' ? 'Shortened' : 'Rewritten' }} suggestion</strong>
            <span class="small text-muted">{{ mb_strlen($current) }} → {{ mb_strlen($s->offered_text) }} characters{{ $limit ? ', limit '.number_format($limit) : '' }}</span>
        </div>
        <div class="row g-3 cmp">
            <div class="col-md-6"><h4 class="small text-muted">Your text now</h4><div dir="auto" style="white-space:pre-wrap">{!! $diff['mine'] !!}</div></div>
            <div class="col-md-6"><h4 class="small text-muted">Suggestion</h4><div dir="auto" style="white-space:pre-wrap">{!! $diff['theirs'] !!}</div></div>
        </div>
        @if ($over)<div class="small text-danger mt-2">Over the {{ ucfirst($target) }} limit. Press Shorten with AI to get one that fits.</div>@endif
        <div class="d-flex gap-2 flex-wrap mt-3">
            <form method="post" action="{{ route('ai.apply', $s) }}" onsubmit="return confirm('Replace your text with the suggestion? Your current text is saved in History first.')">@csrf
                <button class="btn btn-primary btn-sm rounded-pill" @disabled($over)>Apply</button></form>
            <form method="post" action="{{ route('ai.dismiss', $s) }}">@csrf<button class="btn btn-outline-secondary btn-sm rounded-pill">Dismiss</button></form>
        </div>
        <div class="small text-muted mt-2">Made by {{ \App\Settings\AiProviders::ALL[$s->provider]['label'] }} ({{ $s->model }}) with your key. Your post text{{ $s->style_note ? ' and '.$s->style_note : '' }} was sent to it.
        @if ($s->style_note)<br>In my style: {{ $s->style_note }}.@endif @if ($s->instruction)<br>Your instruction: <span dir="auto">{{ $s->instruction }}</span>@endif</div>
    </div></div>
@endif
