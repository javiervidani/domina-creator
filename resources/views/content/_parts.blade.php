<div class="card"><div class="card-body">
    <div class="d-flex justify-content-between mb-2"><strong>Sources</strong><span class="small text-muted">{{ $content->parts->count() }}</span></div>
    @forelse ($content->parts as $part)
        <div class="border rounded-3 p-2 mb-2 bg-white">
            <div class="d-flex align-items-center gap-1 small text-muted">
                <span>{{ $loop->iteration }}.</span><span class="cid">{{ $part->label }}</span>
                <span class="ms-auto d-flex gap-1">
                    @unless ($loop->first)
                    <form method="post" action="{{ route('content.parts.move', [$content, $part]) }}">@csrf<input type="hidden" name="dir" value="up"><button class="icon-btn" title="Move up">↑</button></form>
                    @endunless
                    @unless ($loop->last)
                    <form method="post" action="{{ route('content.parts.move', [$content, $part]) }}">@csrf<input type="hidden" name="dir" value="down"><button class="icon-btn" title="Move down">↓</button></form>
                    @endunless
                    <form method="post" action="{{ route('content.parts.remove', [$content, $part]) }}">@csrf @method('DELETE')<button class="icon-btn text-danger" title="Remove from the list">×</button></form>
                </span>
            </div>
            <div class="small mt-1" dir="auto" style="max-height:3.2em;overflow:hidden">{{ \Illuminate\Support\Str::limit($part->text, 120) }}</div>
        </div>
    @empty
        <div class="small text-muted mb-2">One piece of text. Add a source to build a combined text.</div>
    @endforelse

    @if ($content->parts->isNotEmpty())
        <form method="post" action="{{ route('content.parts.rebuild', $content) }}" class="mb-2"
              onsubmit="return confirm('Replace the master text with the sources in this order?')">@csrf
            <button class="btn btn-sm btn-outline-secondary rounded-pill w-100">Rebuild text from sources</button></form>
    @endif

    <details class="mb-1"><summary class="btn btn-sm btn-outline-primary rounded-pill w-100">+ Add source</summary>
        <form method="post" action="{{ route('content.parts.add', $content) }}" class="mt-2">@csrf<input type="hidden" name="kind" value="library">
            <label class="small text-muted">From the Library</label>
            <select name="source_id" class="form-select form-select-sm mb-1" dir="auto">@foreach ($others as $o)<option value="{{ $o->id }}">CNT-{{ $o->id }} {{ \Illuminate\Support\Str::limit($o->title, 40) }}</option>@endforeach</select>
            <button class="btn btn-sm btn-primary rounded-pill">Add</button></form>
        <form method="post" action="{{ route('content.parts.add', $content) }}" class="mt-2">@csrf<input type="hidden" name="kind" value="paste">
            <label class="small text-muted">Paste text</label>
            <textarea name="text" class="form-control form-control-sm mb-1" rows="3" dir="auto" required></textarea>
            <button class="btn btn-sm btn-primary rounded-pill">Add</button></form>
        <form method="post" action="{{ route('content.parts.add', $content) }}" class="mt-2">@csrf<input type="hidden" name="kind" value="transcript">
            <label class="small text-muted">Video transcript (paste it)</label>
            <textarea name="text" class="form-control form-control-sm mb-1" rows="3" dir="auto" required></textarea>
            <button class="btn btn-sm btn-primary rounded-pill">Add</button></form>
    </details>
    <div class="small text-muted mt-2">Adding a source appends its text. Moving or removing one only changes this list.</div>
</div></div>
