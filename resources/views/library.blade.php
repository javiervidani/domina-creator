@extends('layouts.app')
@section('title', 'Library')
@section('content')
@php($platforms = ['instagram' => 'IG', 'youtube' => 'YT', 'telegram' => 'TG'])
<div class="d-flex justify-content-between align-items-end mb-3">
    <div><h1 class="h3 mb-0">Library</h1>
        <div class="text-muted small">Everything imported from your accounts. One ID per piece of content.</div></div>
</div>

<form method="get" class="d-flex gap-2 flex-wrap align-items-center mb-3">
    <input name="q" value="{{ $f['q'] ?? '' }}" class="form-control" style="max-width:320px" placeholder="Search titles and texts" dir="auto">
    <select name="account" class="form-select w-auto">
        <option value="">All accounts</option>
        @foreach ($accounts as $a)<option value="{{ $a->id }}" @selected(($f['account'] ?? '') == $a->id)>{{ ucfirst($a->platform) }} {{ '@'.$a->handle }}</option>@endforeach
    </select>
    <select name="status" class="form-select w-auto">
        <option value="">Any status</option>
        <option value="imported" @selected(($f['status'] ?? '') === 'imported')>Imported</option>
        <option value="draft" @selected(($f['status'] ?? '') === 'draft')>Draft</option>
    </select>
    <div class="form-check"><input type="checkbox" class="form-check-input" id="chg" name="changed" value="1" @checked(! empty($f['changed']))>
        <label class="form-check-label" for="chg">Source changed</label></div>
    <button class="btn btn-outline-primary rounded-pill">Filter</button>
    <span class="vr"></span>
    <a class="btn btn-sm {{ ($f['view'] ?? '') === 'review' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill" href="{{ route('library', ['view' => 'review']) }}">Needs review</a>
    <a class="btn btn-sm btn-outline-secondary rounded-pill" href="{{ route('library') }}">Clear</a>
</form>

<form method="post" id="bulk">
    @csrf
    <div class="selbar" id="selbar" hidden>
        <div class="selbar-main">
            <span id="selcount" class="fw-semibold">0 selected</span>
            <button class="btn btn-primary btn-sm rounded-pill" formaction="{{ route('library.combine') }}">Combine</button>
            <button type="button" class="btn btn-sm ms-auto" id="clearsel">Clear</button>
        </div>
        <details class="selbar-more">
            <summary class="small">Tag or add to a collection</summary>
            <div class="selbar-grid">
                <div class="input-group input-group-sm">
                    <input name="tag" class="form-control" placeholder="Tag name" dir="auto">
                    <button class="btn btn-outline-primary" formaction="{{ route('library.tag') }}">Tag</button>
                </div>
                <select name="collection_id" class="form-select form-select-sm" dir="auto">
                    <option value="">Choose a collection…</option>
                    @foreach ($collections as $col)<option value="{{ $col->id }}">{{ $col->name }}</option>@endforeach
                </select>
                <div class="input-group input-group-sm">
                    <input name="new_name" class="form-control" placeholder="or a new collection" dir="auto">
                    <button class="btn btn-outline-primary" formaction="{{ route('library.collection') }}">Add</button>
                </div>
            </div>
        </details>
    </div>

    <div class="card">
        @forelse ($items as $item)
            @php($on = $item->copies->pluck('platform')->all())
            <div class="lib-row">
                <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="form-check-input rowsel" aria-label="Select">
                <a class="row-link" href="{{ route('content.show', $item) }}">
                    <div class="t" dir="auto">{{ $item->title }} <span class="cid">{{ $item->platformId() }}</span>
                        @foreach ($item->tags as $tag)<span class="pill blue">{{ $tag->name }}</span> @endforeach</div>
                    <div class="s" dir="auto">{{ \Illuminate\Support\Str::limit($item->master_text, 140) }}</div>
                </a>
                <div class="where d-flex gap-1">
                    @foreach ($platforms as $key => $short)
                        <span class="pf {{ $key }} {{ in_array($key, $on) ? '' : 'off' }}" title="{{ ucfirst($key) }}{{ in_array($key, $on) ? '' : ' (not there)' }}">{{ $short }}</span>
                    @endforeach
                </div>
                <div class="st">
                    @if ($item->source_changed)<span class="pill warn">Source changed</span>
                    @elseif ($item->status === 'draft')<span class="pill blue">Draft</span>
                    @else<span class="pill">Imported</span>@endif
                </div>
            </div>
        @empty
            <div class="p-4 text-muted">Nothing matches. Connect an account on the Accounts page, or change the filters.</div>
        @endforelse
    </div>
</form>

<div class="mt-3">{{ $items->links('pagination::bootstrap-5') }}</div>

<script>
(function () {
    var boxes = document.querySelectorAll('.rowsel'), bar = document.getElementById('selbar');
    function update() {
        var n = document.querySelectorAll('.rowsel:checked').length;
        bar.hidden = n === 0;
        document.getElementById('selcount').textContent = n + ' selected';
    }
    boxes.forEach(function (b) { b.addEventListener('change', update); });
    document.getElementById('clearsel').addEventListener('click', function () { boxes.forEach(function (b) { b.checked = false; }); update(); });
})();
</script>
@endsection
