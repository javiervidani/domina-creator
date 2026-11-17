@extends('layouts.base')

@section('body')
<nav class="navbar bg-white border-bottom px-3">
    <span class="navbar-brand mb-0 h1">Domina Creator</span>
    <div class="me-auto ms-3">
        <a class="me-3" href="{{ route('library') }}">Library</a>
        <a href="{{ route('accounts') }}">Accounts</a>
    </div>
    <form method="post" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-sm btn-outline-secondary rounded-pill">Sign out</button>
    </form>
</nav>
<main class="container py-4">
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    @yield('content')
</main>
@endsection
