@extends('layouts.app')
@section('title', 'Settings')
@section('content')
<h1 class="h3 mb-3">Settings</h1>
<ul class="nav nav-tabs mb-3">
    @foreach (['profile' => 'Profile', 'notify' => 'Notifications', 'ai' => 'AI keys', 'pictures' => 'Pictures', 'team' => 'Team', 'data' => 'Data'] as $id => $label)
        <li class="nav-item"><button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#s-{{ $id }}" type="button">{{ $label }}</button></li>
    @endforeach
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="s-profile">@include('settings._profile')</div>
    <div class="tab-pane fade" id="s-notify">@include('settings._notifications')</div>
    <div class="tab-pane fade" id="s-ai">@include('settings._ai')</div>
    <div class="tab-pane fade" id="s-pictures">@include('settings._pictures')</div>
    <div class="tab-pane fade" id="s-data">@include('settings._data')</div>
    <div class="tab-pane fade" id="s-team">
        <div class="card"><div class="card-body">
            <strong>{{ $user->name }}</strong> <span class="pill blue">Owner</span>
            <div class="small text-muted mt-2">Only you can use this workspace for now. Inviting team members, with roles, comes later.</div>
        </div></div>
    </div>
</div>
@endsection
