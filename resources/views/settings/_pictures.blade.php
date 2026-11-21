<div class="card"><div class="card-body">
    <h2 class="h5">Pictures</h2>
    <p class="small text-muted">By default a post's picture is only a link to Instagram, and Instagram's addresses expire. Switch this on to keep a
        <b>small</b> copy of each new post's picture (at most {{ \App\Media\ImageKeeper::MAX_SIDE }} px wide, about 25 to 60 KB). Never the original, never a video.
        The copy is private, used only in the editor and when sending to other networks, and removed with the post or when you delete your history.</p>
    <form method="post" action="{{ route('settings.pictures') }}">@csrf @method('PATCH')
        <div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="keep_images" name="keep_images" value="1" @checked($pictures['on'])>
            <label class="form-check-label" for="keep_images">Keep a small copy of new pictures</label></div>
        <button class="btn btn-primary rounded-pill">Save</button>
    </form>
    <div class="small text-muted mt-3">Using {{ number_format($pictures['bytes'] / 1048576, 1) }} MB of {{ intdiv(\App\Media\ImageKeeper::BUDGET, 1048576) }} MB
        ({{ $pictures['count'] }} pictures). When it is full, new pictures stay as links.</div>
    @if ($pictures['count'])
        <form method="post" action="{{ route('settings.pictures.delete') }}" class="mt-2" onsubmit="return confirm('Delete all kept pictures? Posts will show their picture by link again.')">@csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger rounded-pill">Delete kept pictures</button></form>
    @endif
</div></div>
