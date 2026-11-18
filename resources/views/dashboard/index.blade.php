@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<h1 class="h3 mb-3">Dashboard</h1>

@unless ($hasAccounts)
    <div class="card mb-3"><div class="card-body">No account connected yet. <a href="{{ route('onboarding') }}">Start the setup</a>.</div></div>
@endunless

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card mb-3"><div class="card-body">
            <h2 class="h5">Needs your attention</h2>
            @forelse ($attention as $item)
                <a href="{{ $item['url'] }}" class="d-flex gap-2 align-items-start py-2 border-top text-decoration-none text-body">
                    <span class="pill {{ $item['tone'] }} flex-shrink-0">{{ ['account' => 'Account', 'failed' => 'Failed', 'draft' => 'Draft', 'changed' => 'Changed'][$item['kind']] }}</span>
                    <span dir="auto">{{ $item['text'] }}</span>
                </a>
            @empty
                <div class="text-muted small">Nothing needs you right now.</div>
            @endforelse
        </div></div>

        <div class="card"><div class="card-body">
            <h2 class="h5">Recent imports</h2>
            @forelse ($recent as $c)
                <a href="{{ route('content.show', $c) }}" class="d-flex gap-2 py-2 border-top text-decoration-none text-body">
                    <span class="cid flex-shrink-0">{{ $c->platformId() }}</span>
                    <span dir="auto" class="text-truncate">{{ $c->title }}</span>
                </a>
            @empty
                <div class="text-muted small">Nothing imported yet.</div>
            @endforelse
            <a href="{{ route('library') }}" class="btn btn-sm btn-outline-secondary rounded-pill mt-2">Open the Library</a>
        </div></div>
    </div>

    <div class="col-lg-5">
        <div class="card"><div class="card-body">
            <h2 class="h5">How content moves</h2>
            @forelse ($lines as $line)
                <div class="py-2 border-top" dir="auto">{{ $line }}</div>
            @empty
                <div class="text-muted small">No targets yet. Add one on the <a href="{{ route('accounts') }}">Accounts</a> page.</div>
            @endforelse
            <div class="small text-muted mt-2">New targets start as review first. A published copy changes only when you press Re-publish.</div>
        </div></div>
    </div>
</div>
@endsection
