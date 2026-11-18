@extends('layouts.app')
@section('title', 'Collections')
@section('content')
<div class="d-flex justify-content-between align-items-end mb-3 flex-wrap gap-2">
    <div><h1 class="h3 mb-0">Collections</h1>
        <div class="text-muted small">Group content and show it on your own website.</div></div>
    <form method="post" action="{{ route('collections.store') }}" class="d-flex gap-2">@csrf
        <input name="name" class="form-control" placeholder="New collection name" dir="auto" required>
        <button class="btn btn-primary rounded-pill">Create</button>
    </form>
</div>

<div class="row g-3">
@forelse ($collections as $c)
    <div class="col-md-6 col-lg-4"><a href="{{ route('collections.show', $c) }}" class="card h-100 text-decoration-none text-body"><div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <strong dir="auto">{{ $c->name }}</strong>
            <span class="pill {{ $c->visibility === 'private' ? '' : 'good' }}">{{ \App\Models\Collection::VISIBILITY[$c->visibility] }}</span>
        </div>
        @if ($c->description)<div class="small text-muted mt-1" dir="auto">{{ \Illuminate\Support\Str::limit($c->description, 100) }}</div>@endif
        <div class="small text-muted mt-2">{{ $c->items_count }} item{{ $c->items_count === 1 ? '' : 's' }}</div>
    </div></a></div>
@empty
    <div class="col-12"><div class="card"><div class="card-body text-muted">No collections yet. Create one above, or select posts in the Library and use Add to collection.</div></div></div>
@endforelse
</div>
@endsection
