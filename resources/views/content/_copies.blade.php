<div class="small text-muted mb-2">One ID: <span class="cid">{{ $content->platformId() }}</span>. Every copy on another account is linked to it.</div>
@forelse ($content->copies as $copy)
    <div class="border rounded-3 p-2 mb-2">
        <div class="d-flex align-items-center gap-2">
            <span class="pf {{ $copy->platform }}">{{ strtoupper(substr($copy->platform, 0, 2)) }}</span>
            <strong>{{ ucfirst($copy->platform) }}</strong>
            <span class="ms-auto pill {{ $copy->is_origin ? 'blue' : '' }}">{{ $copy->is_origin ? 'Source' : 'Linked' }}</span>
        </div>
        <div class="small text-muted mt-1">{{ $copy->is_origin ? 'Original post, imported automatically.' : 'Same text found on another account.' }}</div>
        @if ($copy->permalink)<a class="btn btn-sm btn-outline-secondary rounded-pill mt-2" href="{{ $copy->permalink }}" target="_blank" rel="noopener">Open original</a>@endif
    </div>
@empty
    <div class="small text-muted">No linked copies. This text was written or combined here.</div>
@endforelse
<div class="small text-muted">Publishing to other accounts arrives in a later step.</div>
