@if ($media && ($media->media_url || $media->thumbnail_url))
<div class="card mb-3"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>Media</strong>
        <span class="small text-muted">Linked from {{ ucfirst($media->platform) }}, not copied</span>
    </div>
    {{-- The address belongs to the network and can expire, so a broken image just hides and the link below stays. --}}
    @if ($media->media_type === 'VIDEO' && $media->media_url)
        <video controls preload="none" playsinline class="w-100 rounded-3 media-box" style="background:#000"
               @if ($media->thumbnail_url) poster="{{ $media->thumbnail_url }}" @endif src="{{ $media->media_url }}"></video>
    @else
        <img src="{{ $media->media_url ?: $media->thumbnail_url }}" alt="" loading="lazy" referrerpolicy="no-referrer"
             class="w-100 rounded-3 media-box" style="object-fit:contain;background:#f1f3f4" onerror="this.style.display='none'">
    @endif
    @if ($media->media_type === 'CAROUSEL_ALBUM')<div class="small text-muted mt-1">A carousel: only the first picture is shown.</div>@endif
    @if ($media->permalink)<a class="small" href="{{ $media->permalink }}" target="_blank" rel="noopener">Open the original post</a>@endif
</div></div>
@endif
