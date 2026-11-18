@extends('layouts.app')
@section('title', 'Recycle bin')
@section('content')
@php($left = fn ($item) => max(0, $days - (int) $item->deleted_at->diffInDays(now())))
<h1 class="h3 mb-1">Recycle bin</h1>
<p class="text-muted small">Deleted posts and collections are kept for {{ $days }} days, then removed for good.</p>

<h2 class="h5 mt-3">Posts</h2>
<div class="card mb-3"><div class="card-body">
    @forelse ($contents as $c)
        <div class="d-flex align-items-center gap-2 py-2 {{ $loop->first ? '' : 'border-top' }} flex-wrap">
            <span class="cid">{{ $c->platformId() }}</span>
            <span class="flex-grow-1" dir="auto">{{ $c->title }}</span>
            <span class="small text-muted">{{ $left($c) }} days left</span>
            <form method="post" action="{{ route('recycle.restore', ['content', $c->id]) }}">@csrf<button class="btn btn-sm btn-outline-primary rounded-pill">Restore</button></form>
            <form method="post" action="{{ route('recycle.forget', ['content', $c->id]) }}" onsubmit="return confirm('Delete for good? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger rounded-pill">Delete for good</button></form>
        </div>
    @empty
        <div class="text-muted small">No deleted posts.</div>
    @endforelse
</div></div>

<h2 class="h5">Collections</h2>
<div class="card"><div class="card-body">
    @forelse ($collections as $c)
        <div class="d-flex align-items-center gap-2 py-2 {{ $loop->first ? '' : 'border-top' }} flex-wrap">
            <span class="flex-grow-1" dir="auto">{{ $c->name }}</span>
            <span class="small text-muted">{{ $left($c) }} days left</span>
            <form method="post" action="{{ route('recycle.restore', ['collection', $c->id]) }}">@csrf<button class="btn btn-sm btn-outline-primary rounded-pill">Restore</button></form>
            <form method="post" action="{{ route('recycle.forget', ['collection', $c->id]) }}" onsubmit="return confirm('Delete for good? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger rounded-pill">Delete for good</button></form>
        </div>
    @empty
        <div class="text-muted small">No deleted collections.</div>
    @endforelse
</div></div>
@endsection
