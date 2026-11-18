@forelse ($content->versions as $v)
    <details class="border-top py-2">
        <summary class="small">
            <strong>{{ $v->note }}</strong>@if ($v->target) <span class="pill">{{ \App\Content\Targets::LABELS[$v->target] ?? $v->target }}</span>@endif
            <span class="text-muted d-block">{{ $v->updated_at->diffForHumans() }}</span>
        </summary>
        <div class="small mt-2" dir="auto" style="white-space:pre-wrap;max-height:200px;overflow:auto">{{ $v->text }}</div>
    </details>
@empty
    <div class="small text-muted">No history yet. Your first edit appears here.</div>
@endforelse
