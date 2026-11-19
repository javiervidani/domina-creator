@extends('layouts.app')
@section('title', $collection->name)
@section('content')
<a href="{{ route('collections') }}" class="btn btn-sm btn-outline-secondary rounded-pill mb-3">← Collections</a>
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <h1 class="h3 mb-0" dir="auto">{{ $collection->name }}</h1>
    <span class="pill {{ $collection->visibility === 'private' ? '' : 'good' }}">{{ \App\Models\Collection::VISIBILITY[$collection->visibility] }}</span>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#c-items" type="button">Items ({{ $collection->items->count() }})</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#c-details" type="button">Details</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#c-embed" type="button">Website embed</button></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="c-items"><div class="card"><div class="card-body">
        @forelse ($collection->items as $item)
            <div class="d-flex align-items-center gap-2 py-2 {{ $loop->first ? '' : 'border-top' }}">
                <span class="cid">{{ $item->content?->platformId() }}</span>
                <a href="{{ $item->content ? route('content.show', $item->content) : '#' }}" class="row-link flex-grow-1 text-truncate" dir="auto">{{ $item->content?->title ?? 'Removed' }}</a>
                @unless ($loop->first)<form method="post" action="{{ route('collections.move', [$collection, $item]) }}">@csrf<input type="hidden" name="dir" value="up"><button class="icon-btn" title="Move up">↑</button></form>@endunless
                @unless ($loop->last)<form method="post" action="{{ route('collections.move', [$collection, $item]) }}">@csrf<input type="hidden" name="dir" value="down"><button class="icon-btn" title="Move down">↓</button></form>@endunless
                <form method="post" action="{{ route('collections.remove', [$collection, $item]) }}">@csrf @method('DELETE')<button class="icon-btn text-danger" title="Remove from the collection">×</button></form>
            </div>
        @empty
            <div class="text-muted">Empty. In the Library, select posts and use <b>Add to collection</b>.</div>
        @endforelse
    </div></div></div>

    <div class="tab-pane fade" id="c-details"><div class="card"><div class="card-body">
        <form method="post" action="{{ route('collections.update', $collection) }}">@csrf @method('PATCH')
            <label class="form-label small">Name</label>
            <input name="name" class="form-control mb-2" dir="auto" value="{{ old('name', $collection->name) }}" required>
            <label class="form-label small">Description</label>
            <textarea name="description" class="form-control mb-2" rows="3" dir="auto">{{ old('description', $collection->description) }}</textarea>
            <label class="form-label small">Cover picture link (https, not copied)</label>
            <input name="cover_url" class="form-control mb-2" value="{{ old('cover_url', $collection->cover_url) }}">
            <label class="form-label small">Who can see it</label>
            <select name="visibility" class="form-select mb-1">
                @foreach (\App\Models\Collection::VISIBILITY as $v => $label)<option value="{{ $v }}" @selected($collection->visibility === $v)>{{ $label }}</option>@endforeach
            </select>
            <div class="small text-muted mb-3">Private: nobody but you. Anyone with the link: the embed and feeds work for people who have the link. Public: also available at a readable address.</div>
            <button class="btn btn-primary rounded-pill">Save</button>
        </form>
        <form method="post" action="{{ route('collections.destroy', $collection) }}" class="mt-3" onsubmit="return confirm('Move this collection to the Recycle bin? The embed code and feeds stop working until you restore it. Your content stays.')">@csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger rounded-pill">Move to Recycle bin</button></form>
    </div></div></div>

    <div class="tab-pane fade" id="c-embed">@include('collections._embed')</div>
</div>
@endsection
