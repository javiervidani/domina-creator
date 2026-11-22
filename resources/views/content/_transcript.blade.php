@if ($youtubeCopy)
<div class="card mb-3"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <strong>Transcript</strong>
        <form method="post" action="{{ route('transcript.fetch', $content) }}">@csrf
            <button class="btn btn-sm btn-outline-primary rounded-pill">{{ $transcript ? 'Get it again' : 'Get transcript from YouTube' }}</button></form>
    </div>
    @if ($transcript)
        <div class="small text-muted mb-2">From your YouTube captions{{ $transcript->language ? ' ('.$transcript->language.')' : '' }}.
            @if ($transcript->kind === 'asr')<b>Automatic captions: they can contain mistakes.</b>@endif</div>
        <details class="mb-2"><summary class="small">Show the text ({{ number_format(mb_strlen($transcript->text)) }} characters)</summary>
            <div dir="auto" class="mt-2" style="white-space:pre-wrap">{{ $transcript->text }}</div></details>
        <div class="d-flex gap-2 flex-wrap">
            @foreach (['append' => 'Add below my text', 'replace' => 'Use as my text'] as $mode => $label)
                <form method="post" action="{{ route('transcript.use', [$content, $transcript]) }}" onsubmit="return confirm('{{ $mode === 'replace' ? 'Replace the master text with the transcript?' : 'Add the transcript below the master text?' }} Your current text is saved in History first.')">@csrf
                    <input type="hidden" name="mode" value="{{ $mode }}"><button class="btn btn-sm btn-outline-secondary rounded-pill">{{ $label }}</button></form>
            @endforeach
            <form method="post" action="{{ route('transcript.destroy', [$content, $transcript]) }}">@csrf @method('DELETE')
                <button class="btn btn-sm btn-link text-danger">Remove</button></form>
        </div>
    @else
        <div class="small text-muted">Pulls the captions of this video from your channel. It needs the "Allow transcripts" permission (Accounts page).</div>
    @endif
</div></div>
@endif
