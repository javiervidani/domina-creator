@extends('layouts.guest')
@section('title', 'Privacy')
@section('heading', 'Privacy')
@section('content')
{{-- Draft written from how the platform works. The owner must review it and add contact details before it is final. --}}
<p>Domina Creator is a private workspace for one creator. Your data is kept in a database schema that only your workspace can read.</p>
<h2 class="h6">What we store</h2>
<ul>
    <li>Your name and email, and a hashed password.</li>
    <li>For each connected account: its id, username and an access token. Tokens are encrypted and are never exported.</li>
    <li>The posts and texts you import or write, and your edits to them.</li>
</ul>
<h2 class="h6">What we do with it</h2>
<p>We read your posts so you can edit them in one place, and we only publish to another account when you approve it. We do not sell or share your data.</p>
<h2 class="h6">Removing your data</h2>
<ul>
    <li>Disconnecting an account on the Accounts page deletes its token. Removing the app in Instagram does the same.</li>
    <li>Deleting your history keeps it recoverable for 60 days, then it is permanently erased. Backups are rotated out after that.</li>
</ul>
<p class="mb-0 small text-muted">Contact: add the owner's email here.</p>
@endsection
