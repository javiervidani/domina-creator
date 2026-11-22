<div class="card mb-3"><div class="card-body">
    <h2 class="h5">My style</h2>
    <p class="small text-muted mb-2">Teach the AI how <b>you</b> write. Mark 10 to 30 of your own posts as <b>my voice</b> (the button on a post, or select posts in the Library). From them you get a short
        style guide that you can read and edit, and a few of your real posts are added as examples whenever you ask for Rewrite or Shorten with "In my style".
        For a YouTube video, its <b>transcript</b> (when you fetched one) is used instead of its description, because it is how you actually speak. In the Library, use <b>Get transcripts</b> on selected YouTube posts, then <b>Mark as my voice</b>. The AI only suggests; nothing changes until you press Apply. When you use it, your guide and a few of your posts are sent to the AI provider you chose.</p>
    <div class="small"><b>{{ $style['markedCount'] }}</b> post{{ $style['markedCount'] === 1 ? '' : 's' }} marked as my voice.
        @if ($style['markedCount'] < 10)<span class="text-muted">More is better: aim for 10 to 30.</span>@endif</div>
    @if ($style['marked']->isNotEmpty())
        <details class="mt-2"><summary class="small"><span class="pill">Show the marked posts</span></summary>
            <ul class="small mt-2 mb-0">@foreach ($style['marked'] as $post)<li><a href="{{ route('content.show', $post) }}" dir="auto">{{ $post->title }}</a></li>@endforeach</ul></details>
    @endif
</div></div>

<div class="card mb-3"><div class="card-body">
    <h3 class="h6">Style guide</h3>
    @if ($style['pending'])
        <div class="banner mb-2"><div>Writing a draft from your posts… Reload this page in a moment. The queue worker must be running.</div></div>
    @elseif ($style['error'])
        <div class="banner mb-2"><div class="text-danger">{{ $style['error'] }}</div></div>
    @endif
    @if ($style['isDraft'])<div class="small text-muted mb-2"><b>Draft from your posts.</b> Read it, change what is not you, then save it.</div>@endif
    <form method="post" action="{{ route('style.save') }}">@csrf @method('PUT')
        <textarea name="style_guide" rows="9" maxlength="4000" dir="auto" class="form-control" placeholder="Empty for now. Press &quot;Write a draft from my posts&quot;, or write your own description of how you write.">{{ $style['guide'] }}</textarea>
        <div class="d-flex gap-2 flex-wrap mt-2">
            <button class="btn btn-primary btn-sm rounded-pill">Save style guide</button>
        </div>
    </form>
    <div class="d-flex gap-2 flex-wrap mt-2">
        <form method="post" action="{{ route('style.draft') }}" onsubmit="return confirm('Write a new draft from your marked posts? The text of up to 30 posts is sent to your AI provider. Your saved guide is not changed until you save the draft.')">@csrf
            <button class="btn btn-sm btn-outline-primary rounded-pill" @disabled($style['pending'])>Write a draft from my posts</button></form>
        @if ($style['isDraft'] || $style['error'])
            <form method="post" action="{{ route('style.discard') }}">@csrf<button class="btn btn-sm btn-outline-secondary rounded-pill">Discard the draft</button></form>
        @endif
    </div>
</div></div>

<div class="card"><div class="card-body">
    <h3 class="h6">Saved instructions</h3>
    <p class="small text-muted">Short instructions you reuse, for example "My usual rewrite: warm, short sentences". Pick one next to Rewrite or Shorten in the editor; the default is preselected.</p>
    @foreach ($style['instructions'] as $i)
        <div class="border rounded-3 p-2 mb-2">
            <div class="d-flex align-items-center gap-2 flex-wrap"><strong dir="auto">{{ $i->name }}</strong>@if ($i->is_default)<span class="pill blue">Default</span>@endif
                <span class="ms-auto d-flex gap-2">
                    @unless ($i->is_default)<form method="post" action="{{ route('instructions.default', $i) }}">@csrf<button class="btn btn-sm btn-outline-secondary rounded-pill">Make default</button></form>@endunless
                    <form method="post" action="{{ route('instructions.destroy', $i) }}" onsubmit="return confirm('Remove this instruction?')">@csrf @method('DELETE')<button class="btn btn-sm btn-link text-danger">Remove</button></form>
                </span></div>
            <div class="small text-muted mt-1" dir="auto">{{ $i->text }}</div>
        </div>
    @endforeach
    <form method="post" action="{{ route('instructions.store') }}" class="mt-2">@csrf
        <input name="name" maxlength="60" required dir="auto" class="form-control form-control-sm mb-2" placeholder="Name, for example My usual rewrite">
        <textarea name="text" rows="2" maxlength="600" required dir="auto" class="form-control form-control-sm mb-2" placeholder="The instruction"></textarea>
        <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_default" value="1" id="instr-default"><label class="form-check-label small" for="instr-default">Use it by default</label></div>
        <button class="btn btn-sm btn-outline-primary rounded-pill">Save instruction</button>
    </form>
</div></div>
