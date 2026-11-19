@extends('layouts.base')

@section('body')
<div class="auth-brand brand">Creator <b>Studio</b></div>
<main class="auth-card card shadow-sm">
    <div class="card-body p-4">
        <h1 class="h4 mb-3">@yield('heading')</h1>
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        @endif
        @yield('content')
    </div>
</main>
@endsection
