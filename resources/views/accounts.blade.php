@extends('layouts.app')
@section('title', 'Accounts')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Accounts</h1>
    <a href="{{ route('accounts.instagram') }}" class="btn btn-primary rounded-pill">Connect Instagram</a>
</div>

<details class="card mb-3"><summary class="card-body py-2" style="cursor:pointer">Connect with an access token (advanced)</summary>
    <div class="card-body pt-0">
        <p class="small text-muted">For an account whose token you already hold, for example one made in the Meta app dashboard. It is checked with Instagram, then stored encrypted.</p>
        <form method="post" action="{{ route('accounts.token') }}" class="row g-2" autocomplete="off">
            @csrf
            <div class="col-md-9"><input type="password" name="token" class="form-control" placeholder="Instagram access token" required autocomplete="off"></div>
            <div class="col-auto"><button class="btn btn-primary rounded-pill">Connect</button></div>
        </form>
    </div>
</details>

<details class="card mb-3"><summary class="card-body py-2" style="cursor:pointer">Connect Telegram (publish to a channel or group)</summary>
    <div class="card-body pt-0">
        <p class="small text-muted">Make a bot with @BotFather, add it as an admin of your channel or group, then enter the bot token and the channel (for example <code>@mychannel</code>) or its numeric id. The token is checked with Telegram and stored encrypted.</p>
        <form method="post" action="{{ route('accounts.telegram') }}" class="row g-2" autocomplete="off">
            @csrf
            <div class="col-md-5"><input type="password" name="bot_token" class="form-control" placeholder="Bot token" required autocomplete="off"></div>
            <div class="col-md-4"><input name="chat_id" class="form-control" placeholder="@channel or chat id" required></div>
            <div class="col-auto"><button class="btn btn-primary rounded-pill">Connect</button></div>
        </form>
    </div>
</details>

@php($at = fn ($a) => ($a->platform === 'instagram' ? '@' : '').$a->handle)
@forelse ($accounts as $account)
    @php($h = $health[$account->id])
    <div class="card mb-3"><div class="card-body">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <strong dir="auto">{{ ucfirst($account->platform) }} {{ $at($account) }}</strong>
            @if ($h === 'ok')<span class="badge text-bg-success">OK</span>
            @elseif ($h === 'expiring')<span class="badge text-bg-warning">Token expiring</span>
            @else<span class="badge text-bg-danger">Reconnect</span>@endif
            @if ($account->is_source_of_truth)<span class="badge text-bg-primary">Source of truth</span>@endif
            @if ($account->token?->expires_at)<small class="text-muted">token valid until {{ $account->token->expires_at->format('Y-m-d') }}</small>
            @elseif ($account->token && $account->platform === 'instagram')<small class="text-muted">token expiry not known yet</small>@endif
        </div>
        @if ($account->last_error)<div class="text-danger small mb-2">{{ $account->last_error }}</div>@endif
        @if ($h === 'reconnect')<a href="{{ route('accounts.instagram') }}" class="btn btn-sm btn-outline-danger rounded-pill mb-2">Reconnect</a>@endif

        @if ($account->platform === 'instagram')
        <form method="post" action="{{ route('accounts.update', $account) }}" class="row g-2 align-items-center mb-2">
            @csrf @method('PATCH')
            <div class="col-auto form-check ms-2">
                <input type="checkbox" class="form-check-input" name="import_enabled" value="1" id="imp{{ $account->id }}" @checked($account->import_enabled)>
                <label class="form-check-label" for="imp{{ $account->id }}">Import</label>
            </div>
            <div class="col-auto">
                <select name="import_frequency" class="form-select form-select-sm">
                    @foreach (['fifteen_minutes' => 'Every 15 minutes', 'hourly' => 'Hourly', 'twicedaily' => 'Twice a day', 'daily' => 'Daily'] as $v => $l)
                        <option value="{{ $v }}" @selected($account->import_frequency === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-outline-primary rounded-pill">Save</button></div>
        </form>
        @else
        <div class="small text-muted mb-2">A place to publish to. Add it as a target below, then approve each draft in the editor.</div>
        @endif

        <div class="d-flex flex-wrap gap-2 acc-actions">
            @if ($account->platform === 'instagram')
            <form method="post" action="{{ route('accounts.sync', $account) }}">@csrf
                <button class="btn btn-sm btn-primary rounded-pill">Sync now</button></form>
            <form method="post" action="{{ route('accounts.refresh', $account) }}">@csrf
                <button class="btn btn-sm btn-outline-secondary rounded-pill">Refresh token</button></form>
            @endif
            @unless ($account->is_source_of_truth || $account->platform !== 'instagram')
                <form method="post" action="{{ route('accounts.source', $account) }}"
                      onsubmit="return confirm('Make this the source of truth? The badge moves here. Edited texts are never overwritten.')">@csrf
                    <button class="btn btn-sm btn-outline-secondary rounded-pill">Make source of truth</button></form>
            @endunless
            <form method="post" action="{{ route('accounts.destroy', $account) }}" class="ms-auto"
                  onsubmit="return confirm('Disconnect this account? Its token is deleted.')">@csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger rounded-pill">Disconnect</button></form>
        </div>
    </div></div>
@empty
    <div class="card"><div class="card-body text-muted">No accounts connected yet.</div></div>
@endforelse

@if ($accounts->count() > 1)
<h2 class="h5 mt-4">Targets</h2>
<div class="card mb-3"><div class="card-body">
    @foreach ($targets as $t)
        <div class="target-row">
            <div class="target-name" dir="auto">{{ $at($t->source) }} → {{ $at($t->target) }}</div>
            <form method="post" action="{{ route('targets.update', $t) }}">@csrf @method('PATCH')
                <select name="mode" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Mode">
                    <option value="review" @selected($t->mode === 'review')>Review first</option>
                    <option value="auto" @selected($t->mode === 'auto')>Auto</option>
                </select>
            </form>
            <form method="post" action="{{ route('targets.destroy', $t) }}">@csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger rounded-pill">Remove</button></form>
        </div>
    @endforeach
    <form method="post" action="{{ route('targets.store') }}" class="target-add">@csrf
        <div><label class="form-label small mb-1">From</label>
            <select name="source_account_id" class="form-select form-select-sm" dir="auto">@foreach ($accounts as $a)<option value="{{ $a->id }}">{{ $at($a) }}</option>@endforeach</select></div>
        <div><label class="form-label small mb-1">To</label>
            <select name="target_account_id" class="form-select form-select-sm" dir="auto">@foreach ($accounts as $a)<option value="{{ $a->id }}">{{ $at($a) }}</option>@endforeach</select></div>
        <button class="btn btn-sm btn-outline-primary rounded-pill">Add target (review first)</button>
    </form>
</div></div>
@endif

<h2 class="h5 mt-4">Activity</h2>
<div class="card"><div class="card-body py-2">
    @forelse ($runs as $run)
        <div class="run {{ $loop->first ? '' : 'border-top' }}">
            <div class="run-head">
                <span class="small text-muted">{{ $run->started_at->format('M j, H:i') }}</span>
                <span class="pill">{{ str_replace('_', ' ', $run->kind) }}</span>
                @if ($run->status === 'failed')<span class="pill bad">Failed</span>
                @elseif ($run->status === 'ok')<span class="pill good">OK</span>
                @else<span class="pill blue">Running</span>@endif
                <span class="small ms-auto" dir="auto">{{ $run->account ? $at($run->account) : '' }}</span>
            </div>
            @if ($run->status === 'failed')<div class="small text-danger">{{ $run->error }}</div>
            @elseif ($run->status === 'ok' && $run->kind === 'sync')<div class="small text-muted">{{ $run->fetched }} posts seen, {{ $run->created }} new, {{ $run->changed }} changed</div>@endif
        </div>
    @empty
        <div class="text-muted py-2">No runs yet.</div>
    @endforelse
</div></div>
@endsection
