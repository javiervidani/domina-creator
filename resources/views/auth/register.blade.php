@extends('layouts.guest')
@section('title', 'Create account')
@section('heading', 'Create your workspace')
@section('content')
<form method="post" action="{{ route('register') }}">
    @csrf
    <div class="mb-3"><label class="form-label">Name</label>
        <input name="name" value="{{ old('name') }}" class="form-control" required autofocus></div>
    <div class="mb-3"><label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Password (10+ characters)</label>
        <input type="password" name="password" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Repeat password</label>
        <input type="password" name="password_confirmation" class="form-control" required></div>
    <button class="btn btn-primary rounded-pill w-100">Create account</button>
</form>
<p class="small mt-3 mb-0"><a href="{{ route('login') }}">I already have an account</a></p>
@endsection
