@extends('layouts.guest')
@section('title', 'New password')
@section('heading', 'Choose a new password')
@section('content')
<form method="post" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="mb-3"><label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $email) }}" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">New password (10+ characters)</label>
        <input type="password" name="password" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Repeat password</label>
        <input type="password" name="password_confirmation" class="form-control" required></div>
    <button class="btn btn-primary rounded-pill w-100">Save password</button>
</form>
@endsection
