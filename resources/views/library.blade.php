@extends('layouts.app')
@section('title', 'Library')
@section('content')
@php($platforms = ['instagram' => 'IG', 'youtube' => 'YT', 'telegram' => 'TG'])
<div class="d-flex justify-content-between align-items-end mb-3">
    <div><h1 class="h3 mb-0">Library <a href="{{ route('recycle') }}" class="btn btn-sm btn-link text-muted">Recycle bin</a></h1>
        <div class="text-muted small">Everything imported from your accounts. One ID per piece of content.</div></div>
</div>

<form method="get" class="lib-filters mb-3">
    <div class="input-group">
        <input name="q" value="{{ $f['q'] ?? '' }}" class="form-control" placeholder="Search titles and texts" dir="auto" aria-label="Search">
        <button class="btn btn-primary">Search</button>
    </div>
    <div class="lib-selects">
        <select name="account" class="form-select" aria-label="Account">
            <option value="">All accounts</option>
            @foreach ($accounts as $a)<option value="{{ $a->id }}" @selected(($f['account'] ?? '') == $a->id)>{{ ucfirst($a->platform) }} {{ '@'.$a->handle }}</option>@endforeach
        </select>
        <select name="status" class="form-select" aria-label="Status">
            <option value="">Any status</option>
            <option value="imported" @selected(($f['status'] ?? '') === 'imported')>Imported</option>
            <option value="draft" @selected(($f['status'] ?? '') === 'draft')>Draft</option>
        </select>
        <div class="form-check"><input type="checkbox" class="form-check-input" id="chg" name="changed" value="1" @checked(! empty($f['changed']))>
            <label class="form-check-label" for="chg">Source changed</label></div>
    </div>
    <div class="chips">
        <a class="chip {{ ($f['view'] ?? '') === 'review' ? 'on' : '' }}" href="{{ route('library', ['view' => 'review']) }}">Needs review</a>
        <a class="chip {{ ($f['view'] ?? '') === 'drafts' ? 'on' : '' }}" href="{{ route('library', ['view' => 'drafts']) }}">Drafts @if ($draftCount > 0)<span class="badgex">{{ $draftCount }}</span>@endif</a>
        <a class="chip {{ ($f['view'] ?? '') === 'voice' ? 'on' : '' }}" href="{{ route('library', ['view' => 'voice']) }}">My voice @if ($voiceCount > 0)<span class="badgex">{{ $voiceCount }}</span>@endif</a>
        <a class="chip" href="{{ route('library') }}">Clear</a>
    </div>
</form>

<form method="post" id="bulk">
    @csrf
    <div class="selbar" id="selbar" hidden>
        <div class="selbar-main">
            <span id="selcount" class="fw-semibold">0 selected</span>
            <button class="btn btn-primary btn-sm rounded-pill" formaction="{{ route('library.combine') }}">Combine</button>
            <button class="btn btn-outline-primary btn-sm rounded-pill" formaction="{{ route('library.voice') }}">Mark as my voice</button>
            <button class="btn btn-outline-secondary btn-sm rounded-pill" formaction="{{ route('library.transcripts') }}" title="YouTube posts only">Get transcripts</button>
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
                <button class="btn btn-sm btn-outline-danger" formaction="{{ route('library.delete') }}" onclick="return confirm('Move the selected posts to the Recycle bin for 60 days?')">Move to Recycle bin</button>
            </div>
        </details>
    </div>

    <div class="card">
        @forelse ($items as $item)
            @php($on = $item->copies->pluck('platform')->all())
            @php($cut = str_ends_with($item->title, '…'))
            @php($lead = rtrim($item->title, '…'))
            {{-- The grey line shows what comes after the title. A title cut mid-text keeps the full text; a title that is the whole text needs no line. --}}
            @php($snippet = $cut ? $item->master_text : ltrim(str_starts_with($item->master_text, $lead) ? mb_substr($item->master_text, mb_strlen($lead)) : $item->master_text, " \n.…"))
            <div class="lib-row">
                <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="form-check-input rowsel" aria-label="Select">
                <a class="row-link" href="{{ route('content.show', $item) }}">
                    <div class="t" dir="auto">{{ $item->title }} <span class="cid">{{ $item->platformId() }}</span>
                        @foreach ($item->tags as $tag)<span class="pill blue">{{ $tag->name }}</span> @endforeach
                        @if ($item->my_voice)<span class="pill good">My voice</span>@endif
                        @if ($item->transcripts_count)<span class="pill">Transcript</span>@endif</div>
                    @if ($snippet !== '')<div class="s" dir="auto">{{ \Illuminate\Support\Str::limit($snippet, 140) }}</div>@endif
                </a>
                <div class="lib-meta">
                    <div class="where">
                        @foreach ($platforms as $key => $short)
                            <span class="pf {{ $key }} {{ in_array($key, $on) ? '' : 'off' }}" title="{{ ucfirst($key) }}{{ in_array($key, $on) ? '' : ' (not there)' }}">{{ $short }}</span>
                        @endforeach
                    </div>
                    <div class="st">
                        @if (in_array('youtube', $on) && ! $item->transcripts_count)
                            <button class="btn btn-sm btn-outline-secondary rounded-pill mb-1" name="one" value="{{ $item->id }}" formaction="{{ route('library.transcripts') }}" title="Get the captions of this video as text">Get transcript</button>
                        @endif
                        @if ($item->source_changed)<span class="pill warn">Source changed</span>
                        @elseif ($item->status === 'draft')<span class="pill blue">Draft</span>
                        @else<span class="pill">Imported</span>@endif
                    </div>
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
