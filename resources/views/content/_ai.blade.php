{{-- AI is only a suggestion: it appears next to your text and nothing changes until you press Apply. --}}
@php($s = $suggestions[$target] ?? null)
<div class="d-flex gap-2 flex-wrap mt-2">
    @foreach (['rewrite' => 'Rewrite with AI', 'shorten' => 'Shorten with AI'] as $action => $label)
        <form method="post" action="{{ route('ai.suggest', [$content, $target]) }}">@csrf<input type="hidden" name="action" value="{{ $action }}">
            <button class="btn btn-sm btn-outline-primary rounded-pill" @disabled(! $aiReady || ($s && $s->status === 'pending'))>{{ $label }}</button></form>
    @endforeach
</div>
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
        <div class="small text-muted mt-2">Made by {{ \App\Settings\AiProviders::ALL[$s->provider]['label'] }} ({{ $s->model }}) with your key. Your post text was sent to it.</div>
    </div></div>
@endif
