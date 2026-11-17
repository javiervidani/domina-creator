@extends('layouts.base')

@section('body')
<nav class="navbar bg-white border-bottom px-3">
    <span class="navbar-brand mb-0 h1">Domina Creator</span>
    <form method="post" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-sm btn-outline-secondary rounded-pill">Sign out</button>
    </form>
</nav>
<main class="container py-4">@yield('content')</main>
@endsection
