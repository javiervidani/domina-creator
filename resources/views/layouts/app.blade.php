@extends('layouts.base')

@section('body')
<header class="studio-nav">
    <span class="brand">Creator <b>Studio</b></span>
    <a class="tabx {{ request()->routeIs('dashboard', 'onboarding') ? 'on' : '' }}" href="{{ route('home') }}">Dashboard</a>
    <a class="tabx {{ request()->routeIs('library', 'content.*') ? 'on' : '' }}" href="{{ route('library') }}">Library
        @if ($nav['drafts'] > 0)<span class="badgex" title="Drafts">{{ $nav['drafts'] }}</span>@endif</a>
    <a class="tabx {{ request()->routeIs('accounts') ? 'on' : '' }}" href="{{ route('accounts') }}">Accounts</a>
    <a class="tabx {{ request()->routeIs('settings') ? 'on' : '' }}" href="{{ route('settings') }}">Settings</a>

    <div class="dropdown ms-auto">
        <button class="syncbtn" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="dot {{ $nav['chipOk'] ? '' : 'dot-warn' }}"></span>{{ $nav['chip'] }}
        </button>
        <div class="dropdown-menu dropdown-menu-end p-3" style="min-width:300px">
            <div class="fw-semibold mb-2">Sync status</div>
            @forelse ($nav['accounts'] as $a)
                <div class="d-flex justify-content-between gap-3 py-1 border-top small">
                    <span>{{ ucfirst($a->platform) }} {{ '@'.$a->handle }}</span>
                    <span class="pill {{ $a->status === 'ok' ? 'good' : 'bad' }}">{{ $a->status === 'ok' ? ($a->last_sync_at ? $a->last_sync_at->diffForHumans(short: true) : 'not synced') : 'Reconnect' }}</span>
                </div>
            @empty
                <div class="small text-muted">No accounts connected.</div>
            @endforelse
            <a class="btn btn-sm btn-outline-secondary rounded-pill mt-2" href="{{ route('accounts') }}">Accounts and sync</a>
        </div>
    </div>
    <form method="post" action="{{ route('logout') }}" class="ms-2">@csrf
        <button class="btn btn-sm btn-outline-secondary rounded-pill">Sign out</button></form>
</header>
<main class="page">
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    @yield('content')
</main>
@endsection
