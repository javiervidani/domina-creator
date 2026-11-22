<div class="card mb-3"><div class="card-body">
    <h2 class="h5">Export my data</h2>
    <p class="small text-muted">A zip of plain CSV files (open them in Excel or Google Sheets): your posts, texts, history, transcripts, collections and settings. Account tokens, AI keys and feed links are never included. Pictures are listed by address only.
        <b>Do this before deleting your history</b>: afterwards your workspace is locked.</p>
    <form method="post" action="{{ route('export.download') }}" autocomplete="off">@csrf
        <label class="form-label small">Your password</label>
        <input type="password" name="password" class="form-control mb-2" required autocomplete="current-password">
        @error('export_password')<div class="small text-danger mb-2">{{ $message }}</div>@enderror
        <button class="btn btn-sm btn-outline-primary rounded-pill">Download my data (zip)</button>
    </form>
</div></div>

<div class="card mb-3"><div class="card-body">
    <h2 class="h5">Recycle bin</h2>
    <p class="small text-muted mb-2">Posts and collections you delete are kept for 60 days.</p>
    <a href="{{ route('recycle') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Open the Recycle bin</a>
</div></div>

<div class="card border-danger"><div class="card-body">
    <h2 class="h5 text-danger">Delete all my history</h2>
    <p class="small text-muted">This locks your workspace now and deletes everything permanently after 60 days: posts, collections, settings and AI keys. Connected accounts are disconnected and their tokens removed. Until day 60 you can sign in and restore it. Published posts on other networks are not touched.</p>
    <form method="post" action="{{ route('deletion.request') }}" autocomplete="off">@csrf
        <label class="form-label small">Your password</label>
        <input type="password" name="password" class="form-control mb-2" required>
        <label class="form-label small">Type <b>DELETE</b> to confirm</label>
        <input name="confirm" class="form-control mb-3" autocomplete="off" required>
        <button class="btn btn-danger rounded-pill">Delete my history</button>
    </form>
</div></div>
