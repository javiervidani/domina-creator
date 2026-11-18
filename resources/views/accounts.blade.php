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

@forelse ($accounts as $account)
    @php($h = $health[$account->id])
    <div class="card mb-3"><div class="card-body">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <strong>{{ ucfirst($account->platform) }} {{ '@'.$account->handle }}</strong>
            @if ($h === 'ok')<span class="badge text-bg-success">OK</span>
            @elseif ($h === 'expiring')<span class="badge text-bg-warning">Token expiring</span>
            @else<span class="badge text-bg-danger">Reconnect</span>@endif
            @if ($account->is_source_of_truth)<span class="badge text-bg-primary">Source of truth</span>@endif
            @if ($account->token?->expires_at)<small class="text-muted">token valid until {{ $account->token->expires_at->format('Y-m-d') }}</small>
            @elseif ($account->token)<small class="text-muted">token expiry not known yet</small>@endif
        </div>
        @if ($account->last_error)<div class="text-danger small mb-2">{{ $account->last_error }}</div>@endif
        @if ($h === 'reconnect')<a href="{{ route('accounts.instagram') }}" class="btn btn-sm btn-outline-danger rounded-pill mb-2">Reconnect</a>@endif

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

        <div class="d-flex flex-wrap gap-2">
            <form method="post" action="{{ route('accounts.refresh', $account) }}">@csrf
                <button class="btn btn-sm btn-outline-secondary rounded-pill">Refresh token</button></form>
            <form method="post" action="{{ route('accounts.sync', $account) }}">@csrf
                <button class="btn btn-sm btn-primary rounded-pill">Sync now</button></form>
            @unless ($account->is_source_of_truth)
                <form method="post" action="{{ route('accounts.source', $account) }}"
                      onsubmit="return confirm('Make this the source of truth? The badge moves here. Edited texts are never overwritten.')">@csrf
                    <button class="btn btn-sm btn-outline-secondary rounded-pill">Make source of truth</button></form>
            @endunless
            <form method="post" action="{{ route('accounts.destroy', $account) }}"
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
        <div class="d-flex gap-2 align-items-center mb-2">
            <span>{{ '@'.$t->source->handle }} → {{ '@'.$t->target->handle }}</span>
            <form method="post" action="{{ route('targets.update', $t) }}">@csrf @method('PATCH')
                <select name="mode" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="review" @selected($t->mode === 'review')>Review first</option>
                    <option value="auto" @selected($t->mode === 'auto')>Auto</option>
                </select>
            </form>
            <form method="post" action="{{ route('targets.destroy', $t) }}">@csrf @method('DELETE')
                <button class="btn btn-sm btn-link text-danger">Remove</button></form>
        </div>
    @endforeach
    <form method="post" action="{{ route('targets.store') }}" class="d-flex gap-2 flex-wrap">@csrf
        <select name="source_account_id" class="form-select form-select-sm w-auto">@foreach ($accounts as $a)<option value="{{ $a->id }}">{{ $a->handle }}</option>@endforeach</select>
        <span class="align-self-center">→</span>
        <select name="target_account_id" class="form-select form-select-sm w-auto">@foreach ($accounts as $a)<option value="{{ $a->id }}">{{ $a->handle }}</option>@endforeach</select>
        <button class="btn btn-sm btn-outline-primary rounded-pill">Add target (review first)</button>
    </form>
</div></div>
@endif

<h2 class="h5 mt-4">Activity</h2>
<div class="card"><div class="table-responsive"><table class="table table-sm mb-0">
    <thead><tr><th>When</th><th>Account</th><th>Kind</th><th>Result</th></tr></thead>
    <tbody>
    @forelse ($runs as $run)
        <tr><td>{{ $run->started_at->format('Y-m-d H:i') }}</td><td>{{ '@'.$run->account?->handle }}</td><td>{{ str_replace('_', ' ', $run->kind) }}</td>
            <td>@if ($run->status === 'failed')<span class="text-danger">{{ $run->error }}</span>@elseif ($run->status === 'ok')OK{{ $run->kind === 'sync' ? ', '.$run->fetched.' items seen' : '' }}@else Running @endif</td></tr>
    @empty
        <tr><td colspan="4" class="text-muted">No runs yet.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
@endsection
