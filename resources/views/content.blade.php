@extends('layouts.app')
@section('title', $content->title)
@section('content')
<a href="{{ route('library') }}" class="btn btn-sm btn-outline-secondary rounded-pill mb-3">← Library</a>
<div class="card mb-3"><div class="card-body">
    <h1 class="h4" dir="auto">{{ $content->title }}</h1>
    <div class="mb-3"><span class="cid">{{ $content->platformId() }}</span>
        @if ($content->source_changed)<span class="pill warn">Source changed</span>@endif
        @if ($content->status === 'draft')<span class="pill blue">Draft</span>@endif
        @foreach ($content->tags as $tag)<span class="pill blue">{{ $tag->name }}</span>@endforeach</div>
    <div style="white-space:pre-wrap" dir="auto">{{ $content->master_text }}</div>
    <div class="small text-muted mt-3">Read-only for now. Editing arrives with the Content editor.</div>
</div></div>

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
