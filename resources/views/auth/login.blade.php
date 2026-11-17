@extends('layouts.guest')
@section('title', 'Sign in')
@section('heading', 'Sign in')
@section('content')
<form method="post" action="{{ route('login') }}">
    @csrf
    <div class="mb-3"><label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus></div>
    <div class="mb-3"><label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required></div>
    <div class="form-check mb-3"><input type="checkbox" name="remember" value="1" class="form-check-input" id="remember">
        <label class="form-check-label" for="remember">Stay signed in</label></div>
    <button class="btn btn-primary rounded-pill w-100">Sign in</button>
</form>
<p class="small mt-3 mb-0"><a href="{{ route('password.request') }}">Forgot password?</a> · <a href="{{ route('register') }}">Create account</a></p>
@endsection
