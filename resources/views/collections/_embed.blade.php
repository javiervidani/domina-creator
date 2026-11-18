@php($domains = \App\Collections\AllowedDomains::parse($embed?->allowed_domains))
@php($base = $token ? url('/') : null)
<div class="row g-3">
    <div class="col-lg-5"><div class="card"><div class="card-body">
        <h2 class="h6">Layout and look</h2>
        <form method="post" action="{{ route('collections.embed', $collection) }}">@csrf @method('PATCH')
            <label class="form-label small">Layout</label>
            <select name="layout" class="form-select mb-2"><option value="list" @selected(($embed->layout ?? 'list') === 'list')>List</option><option value="grid" @selected(($embed->layout ?? '') === 'grid')>Grid</option></select>
            <label class="form-label small">Accent colour</label>
            <input type="color" name="accent" class="form-control form-control-color mb-2" value="{{ $embed->accent ?? '#0b57d0' }}">
            <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="show_media" value="1" id="sm" @checked($embed->show_media ?? true)><label class="form-check-label" for="sm">Show the post picture (linked, may stop working when the network's address expires)</label></div>
            <label class="form-label small">Sites allowed to show it (one per line, for example <code>mysite.com</code> or <code>*.mysite.com</code>)</label>
            <textarea name="allowed_domains" class="form-control mb-2" rows="4">{{ old('allowed_domains', $embed->allowed_domains ?? '') }}</textarea>
            <button class="btn btn-primary rounded-pill">Save embed settings</button>
        </form>
    </div></div></div>

    <div class="col-lg-7"><div class="card"><div class="card-body">
        @if ($collection->visibility === 'private')
            <div class="alert alert-warning mb-3">This collection is private, so the embed and feeds return nothing. Change <b>Who can see it</b> on the Details tab.</div>
        @endif
        @unless ($domains)
            <div class="alert alert-info mb-3">No site is allowed yet: browsers will refuse to show the embed anywhere. Add your website above.</div>
        @endunless

        @if ($token)
            @php($src = url('/embed/'.$token))
            <h2 class="h6">Embed code</h2>
            <textarea class="form-control mb-1" rows="6" readonly onclick="this.select()">&lt;iframe id="domina-{{ $collection->id }}" src="{{ $src }}" style="width:100%;height:600px;border:0" loading="lazy"&gt;&lt;/iframe&gt;
&lt;script&gt;addEventListener('message',function(e){if(e.origin==='{{ rtrim(url('/'), '/') }}'&amp;&amp;e.data&amp;&amp;e.data.domina==='height'){document.getElementById('domina-{{ $collection->id }}').style.height=e.data.height+'px'}})&lt;/script&gt;</textarea>
            <div class="small text-muted mb-3">Paste it into your website. The second part resizes the frame to the content.</div>

            <h2 class="h6">Feeds</h2>
            <div class="small">RSS: <a href="{{ url('/feed/'.$token.'/rss') }}" target="_blank" rel="noopener">{{ url('/feed/'.$token.'/rss') }}</a></div>
            <div class="small mb-2">JSON: <a href="{{ url('/feed/'.$token.'/json') }}" target="_blank" rel="noopener">{{ url('/feed/'.$token.'/json') }}</a></div>
            @if ($collection->visibility === 'public')
                <div class="small mb-2">Public address: <a href="{{ url('/c/'.$creatorId.'/'.$collection->slug) }}" target="_blank" rel="noopener">{{ url('/c/'.$creatorId.'/'.$collection->slug) }}</a></div>
            @endif
            <div class="small text-muted mb-2">Keep the link to yourself unless the collection is public: anyone who has it can read the collection.</div>
            <form method="post" action="{{ route('collections.token', $collection) }}" onsubmit="return confirm('Create a new link? The old embed code, RSS and JSON stop working until you update your website.')">@csrf
                <button class="btn btn-sm btn-outline-danger rounded-pill">Create a new link</button></form>
        @else
            <div class="alert alert-secondary mb-2">There is no active link. Create one to get the embed code and feeds.</div>
            <form method="post" action="{{ route('collections.token', $collection) }}">@csrf<button class="btn btn-sm btn-primary rounded-pill">Create a link</button></form>
        @endif
    </div></div></div>
</div>
