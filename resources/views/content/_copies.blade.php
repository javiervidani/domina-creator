<div class="small text-muted mb-2">One ID: <span class="cid">{{ $content->platformId() }}</span>. Every copy on another account is linked to it.</div>
@php($img = $media?->kept_path ? true : \App\Publishing\Publisher::imageUrl($media))
@forelse ($content->copies as $copy)
    @php($isTarget = $copy->link_mode === 'target')
    <div class="border rounded-3 p-2 mb-2">
        <div class="d-flex align-items-center gap-2">
            <span class="pf {{ $copy->platform }}">{{ strtoupper(substr($copy->platform, 0, 2)) }}</span>
            <strong>{{ ucfirst($copy->platform) }}</strong>
            @if (! $isTarget)
                <span class="ms-auto pill {{ $copy->is_origin ? 'blue' : '' }}">{{ $copy->is_origin ? 'Source' : 'Linked' }}</span>
            @elseif ($copy->status === 'draft')<span class="ms-auto pill warn">Draft, review first</span>
            @elseif ($copy->status === 'queued')<span class="ms-auto pill blue">Sending…</span>
            @elseif ($copy->status === 'failed')<span class="ms-auto pill bad">Failed</span>
            @else<span class="ms-auto pill good">Published</span>@endif
        </div>

        @if (! $isTarget)
            <div class="small text-muted mt-1">{{ $copy->is_origin ? 'Original post, imported automatically.' : 'Same text found on another account.' }}</div>
            @if ($copy->permalink)<a class="btn btn-sm btn-outline-secondary rounded-pill mt-2" href="{{ $copy->permalink }}" target="_blank" rel="noopener">Open original</a>@endif
        @else
            @if ($copy->status === 'failed')<div class="small text-danger mt-1">{{ $copy->last_error }}</div>
            @elseif ($copy->status === 'draft')<div class="small text-muted mt-1">Waiting for your approval. Nothing has been sent.</div>
            @elseif ($copy->status === 'published')
                <div class="small text-muted mt-1">Sent {{ $copy->published_at?->diffForHumans() }}.
                    @if ($differs[$copy->id] ?? false)<span class="text-warning-emphasis fw-semibold">Your text now differs from the published copy.</span>@endif</div>
            @endif
            @if ($copy->status !== 'queued')
            <form method="post" action="{{ route('content.publish', [$content, $copy]) }}" class="mt-2"
                  @if ($copy->status === 'published') onsubmit="return confirm('Update the published {{ ucfirst($copy->platform) }} message with your current text?')" @endif>@csrf
                @if ($img && $copy->status !== 'published')
                    <input type="hidden" name="send_image" value="0">
                    <label class="small d-block mb-1"><input type="checkbox" name="send_image" value="1" @checked($copy->send_image)>
                        Send with the picture{{ $media->media_type === 'VIDEO' ? ' (the video\'s cover image)' : '' }}</label>
                @elseif ($copy->status === 'published' && $copy->image_mode)
                    <div class="small text-muted mb-1">Sent with the picture.</div>
                @endif
                <button class="btn btn-sm rounded-pill {{ $copy->status === 'published' ? (($differs[$copy->id] ?? false) ? 'btn-primary' : 'btn-outline-secondary') : 'btn-primary' }}">
                    {{ ['draft' => 'Approve and publish', 'failed' => 'Retry', 'published' => 'Re-publish'][$copy->status] ?? 'Publish' }}</button>
            </form>
            @endif
            @if (in_array($copy->status, ['draft', 'failed'], true))
            <form method="post" action="{{ route('content.copy.remove', [$content, $copy]) }}" class="mt-1" onsubmit="return confirm('Remove this {{ ucfirst($copy->platform) }} copy? Nothing is deleted on {{ ucfirst($copy->platform) }}.')">@csrf @method('DELETE')
                <button class="btn btn-sm btn-link text-danger p-0">Remove</button></form>
            @endif
        @endif
    </div>
@empty
    <div class="small text-muted">No linked copies. This text was written or combined here.</div>
@endforelse

@foreach ($sendable as $account)
    <form method="post" action="{{ route('content.draft', [$content, $account]) }}" class="mb-2">@csrf
        <button class="btn btn-sm btn-outline-primary rounded-pill w-100">Create a draft for {{ ucfirst($account->platform) }} {{ $account->handle }}</button></form>
@endforeach
<div class="small text-muted">A published copy is never changed by itself. You press Re-publish.</div>
