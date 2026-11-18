@extends('layouts.guest')
@section('title', 'History scheduled for deletion')
@section('heading', 'Your history is scheduled for deletion')
@section('content')
<p>Your content, accounts and settings are locked and will be permanently deleted on <b>{{ $purgeAt->format('j F Y') }}</b>.</p>
<p class="small text-muted">Until then you can change your mind. Restoring brings your content back. Your connected accounts were disconnected, so you will need to reconnect them.</p>
<form method="post" action="{{ route('deletion.restore') }}" class="mb-2">@csrf
    <button class="btn btn-primary rounded-pill w-100">Restore my history</button></form>
<form method="post" action="{{ route('logout') }}">@csrf
    <button class="btn btn-outline-secondary rounded-pill w-100">Sign out</button></form>
@endsection
