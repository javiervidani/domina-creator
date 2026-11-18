@extends('layouts.app')
@section('title', 'Set up')
@section('content')
@php($current = collect($steps)->search(fn ($s) => ! $s['done']))
<h1 class="h3 mb-1">Let's set up your workspace</h1>
<p class="text-muted">Four short steps. You can change everything later on the Accounts page.</p>

@foreach ($steps as $i => $step)
<div class="card mb-3 {{ $current === $i ? 'border border-primary' : '' }}"><div class="card-body">
    <div class="d-flex align-items-center gap-2">
        <span class="pill {{ $step['done'] ? 'good' : ($current === $i ? 'blue' : '') }}">{{ $step['done'] ? 'Done' : 'Step '.($i + 1) }}</span>
        <strong>{{ $step['title'] }}</strong>
    </div>

    @if ($current === $i)
        <div class="mt-3">
        @if ($step['key'] === 'connect')
            <p class="small text-muted">You sign in on Instagram and allow read access. Your account must be a Professional account.</p>
            <a href="{{ route('accounts.instagram') }}" class="btn btn-primary rounded-pill">Connect Instagram</a>
            <a href="{{ route('accounts') }}" class="btn btn-outline-secondary rounded-pill">Other ways to connect</a>

        @elseif ($step['key'] === 'source')
            <p class="small text-muted">The source of truth is the account your content starts from.</p>
            @foreach ($accounts as $a)
                <form method="post" action="{{ route('accounts.source', $a) }}" class="mb-1">@csrf
                    <button class="btn btn-sm btn-primary rounded-pill">Use {{ ucfirst($a->platform) }} {{ '@'.$a->handle }}</button></form>
            @endforeach

        @elseif ($step['key'] === 'targets')
            @if ($accounts->count() < 2)
                <p class="small text-muted">Targets are other accounts your content can go to, for example a Telegram channel. Connect one on the Accounts page, or skip this and do it later.</p>
                <a href="{{ route('accounts') }}" class="btn btn-outline-primary rounded-pill">Connect Telegram</a>
            @else
                <p class="small text-muted">New targets start as <b>review first</b>: nothing is sent until you approve it. You can switch a target to Auto.</p>
                <form method="post" action="{{ route('targets.store') }}" class="d-flex gap-2 flex-wrap mb-2">@csrf
                    <select name="source_account_id" class="form-select form-select-sm w-auto">@foreach ($accounts as $a)<option value="{{ $a->id }}">{{ $a->handle }}</option>@endforeach</select>
                    <span class="align-self-center">to</span>
                    <select name="target_account_id" class="form-select form-select-sm w-auto">@foreach ($accounts as $a)<option value="{{ $a->id }}">{{ $a->handle }}</option>@endforeach</select>
                    <button class="btn btn-sm btn-primary rounded-pill">Add target</button>
                </form>
            @endif
            <form method="post" action="{{ route('onboarding.skip') }}" class="mt-2">@csrf
                <button class="btn btn-sm btn-link px-0">Skip for now</button></form>

        @elseif ($step['key'] === 'import')
            @php($ig = $accounts->firstWhere('platform', 'instagram'))
            <p class="small text-muted">This reads your latest posts into the Library. It runs in the background, so reload this page in a minute.</p>
            @if ($ig)
                <form method="post" action="{{ route('accounts.sync', $ig) }}">@csrf
                    <button class="btn btn-primary rounded-pill">Import my posts</button></form>
            @endif
        @endif
        </div>
    @endif
</div></div>
@endforeach

@if (collect($steps)->every(fn ($s) => $s['done']))
    <div class="alert alert-success">You are set. <a href="{{ route('dashboard') }}">Go to the dashboard</a> or <a href="{{ route('library') }}">open the Library</a>.</div>
@endif
@endsection
