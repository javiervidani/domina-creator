@extends('layouts.app')
@section('title', $content->title)
@section('content')
@php($limits = \App\Content\Targets::LIMITS)
<div class="ed-head mb-3">
    <a href="{{ route('library') }}" class="btn btn-sm btn-outline-secondary rounded-pill ed-back">← Library</a>
    <div class="ed-title">
        {{-- A textarea so a long title wraps instead of being cut off; it grows with the text (see the script). --}}
        <textarea form="masterForm" name="title" rows="1" dir="auto" aria-label="Title" required>{{ old('title', $content->title) }}</textarea>
        <div><span class="cid">{{ $content->platformId() }}</span>
            @if ($content->status === 'draft')<span class="pill blue">Draft</span>@endif
            @foreach ($content->tags as $tag)<span class="pill blue">{{ $tag->name }}</span>@endforeach</div>
    </div>
    <div class="ed-actions">
        <span class="small text-muted" id="saved">Saved</span>
        <button form="masterForm" class="btn btn-primary btn-sm rounded-pill px-3">Save</button>
        <form method="post" action="{{ route('content.destroy', $content) }}" onsubmit="return confirm('Move this post to the Recycle bin for 60 days?')">@csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm rounded-pill">Delete</button></form>
    </div>
</div>

@if ($content->source_changed && $diff)
<div class="banner mb-3">
    <div><b>The original changed after you imported it.</b> Your text is unchanged.
        <div class="d-flex gap-2 flex-wrap mt-2">
            <form method="post" action="{{ route('content.keep', $content) }}">@csrf<button class="btn btn-sm btn-outline-secondary rounded-pill">Keep mine</button></form>
            <form method="post" action="{{ route('content.take', $content) }}" onsubmit="return confirm('Replace your text with the new original? Your text stays in History.')">@csrf<button class="btn btn-sm btn-outline-secondary rounded-pill">Take the new version</button></form>
            <button class="btn btn-sm btn-outline-secondary rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#compare">Compare</button>
        </div></div>
</div>
<div class="collapse mb-3" id="compare"><div class="card"><div class="card-body"><div class="row g-3 cmp">
    <div class="col-md-6"><h4 class="small text-muted">Your version</h4><div dir="auto" style="white-space:pre-wrap">{!! $diff['mine'] !!}</div></div>
    <div class="col-md-6"><h4 class="small text-muted">Instagram now</h4><div dir="auto" style="white-space:pre-wrap">{!! $diff['theirs'] !!}</div></div>
</div></div></div></div>
@endif

<div class="row g-3">
    <aside class="col-lg-3 order-2 order-lg-1">@include('content._parts')</aside>

    <main class="col-lg-6 order-1 order-lg-2">
        @include('content._media')
        <div class="card"><div class="card-body">
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#t-master" type="button">Master text</button></li>
            @foreach (\App\Content\Targets::LABELS as $key => $label)
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-{{ $key }}" type="button">{{ $label }}</button></li>
            @endforeach
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="t-master">
                <form method="post" id="masterForm" action="{{ route('content.update', $content) }}" data-autosave>
                    @csrf @method('PATCH')
                    <textarea name="master_text" class="form-control" rows="14" dir="auto" required>{{ old('master_text', $content->master_text) }}</textarea>
                    <div class="small text-muted mt-1"><span data-count></span></div>
                </form>
            </div>
            @foreach (\App\Content\Targets::LABELS as $key => $label)
                @php($own = $targetTexts->has($key))
                <div class="tab-pane fade" id="t-{{ $key }}">
                    <form method="post" action="{{ route('content.target', [$content, $key]) }}" data-autosave data-limit="{{ $limits[$key] }}">
                        @csrf @method('PATCH')
                        <div class="form-check mb-2"><input type="checkbox" class="form-check-input same" name="same" value="1" id="same-{{ $key }}" @checked(! $own)>
                            <label class="form-check-label" for="same-{{ $key }}">Same as master text</label></div>
                        <textarea name="text" class="form-control" rows="14" dir="auto" data-master="{{ $content->master_text }}" @readonly(! $own)>{{ $own ? $targetTexts[$key] : $content->master_text }}</textarea>
                        <div class="small text-muted mt-1"><span data-count></span> · {{ $label }} limit {{ number_format($limits[$key]) }}</div>
                    </form>
                </div>
            @endforeach
        </div>
        <div class="small text-muted mt-3">Originals are never changed. Edits live here and are saved as you type.</div>
    </div></div></main>

    <aside class="col-lg-3 order-3"><div class="card"><div class="card-body">
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#r-copies" type="button">Linked copies</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#r-history" type="button">History</button></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="r-copies">@include('content._copies')</div>
            <div class="tab-pane fade" id="r-history">@include('content._history')</div>
        </div>
    </div></div></aside>
</div>
@include('content._script')
@endsection
