@extends('layouts.guest')
@section('title', 'Reset password')
@section('heading', 'Reset your password')
@section('content')
<form method="post" action="{{ route('password.email') }}">
    @csrf
    <div class="mb-3"><label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus></div>
    <button class="btn btn-primary rounded-pill w-100">Send reset link</button>
</form>
<p class="small mt-3 mb-0"><a href="{{ route('login') }}">Back to sign in</a></p>
@endsection
