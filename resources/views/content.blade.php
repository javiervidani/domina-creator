@extends('layouts.app')
@section('title', $content->title)
@section('content')
<a href="{{ route('library') }}" class="btn btn-sm btn-outline-secondary rounded-pill mb-3">← Library</a>
<form method="post" action="{{ route('content.update', $content) }}" class="card mb-3">
    @csrf @method('PATCH')
    <div class="card-body">
        <div class="mb-3"><span class="cid">{{ $content->platformId() }}</span>
            @if ($content->source_changed)<span class="pill warn">Source changed</span>@endif
            @if ($content->status === 'draft')<span class="pill blue">Draft</span>@endif
            @foreach ($content->tags as $tag)<span class="pill blue">{{ $tag->name }}</span>@endforeach</div>
        <label class="form-label small">Title</label>
        <input name="title" class="form-control mb-3" dir="auto" value="{{ old('title', $content->title) }}" required>
        <label class="form-label small">Text</label>
        <textarea name="master_text" class="form-control mb-3" rows="12" dir="auto" required>{{ old('master_text', $content->master_text) }}</textarea>
        <button class="btn btn-primary rounded-pill">Save</button>
        <span class="small text-muted ms-2">Originals are never changed. Per-target versions, history and AI come with the full editor (step 7).</span>
    </div>
</form>

@if ($content->parts->isNotEmpty())
<h2 class="h5">Parts</h2>
@foreach ($content->parts as $part)
    <div class="card mb-2"><div class="card-body py-2">
        <div class="small text-muted">{{ $part->position }}. <span class="cid">{{ $part->label }}</span></div>
        <div dir="auto" style="white-space:pre-wrap">{{ $part->text }}</div>
    </div></div>
@endforeach
@endif

@if ($content->copies->isNotEmpty())
<h2 class="h5 mt-3">Linked copies</h2>
@foreach ($content->copies as $copy)
    <div class="small">{{ ucfirst($copy->platform) }}
        @if ($copy->permalink)<a href="{{ $copy->permalink }}" target="_blank" rel="noopener">open original</a>@endif
        <span class="text-muted">{{ $copy->is_origin ? '(source)' : '(linked automatically)' }}</span></div>
@endforeach
@endif
@endsection
