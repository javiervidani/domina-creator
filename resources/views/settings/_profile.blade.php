<div class="row g-3">
    <div class="col-lg-6"><div class="card"><div class="card-body">
        <h2 class="h5">Profile</h2>
        <form method="post" action="{{ route('settings.profile') }}">@csrf @method('PATCH')
            <label class="form-label small">Name</label>
            <input name="name" class="form-control mb-2" value="{{ old('name', $user->name) }}" required>
            <label class="form-label small">Email</label>
            <input type="email" name="email" class="form-control mb-3" value="{{ old('email', $user->email) }}" required>
            <button class="btn btn-primary rounded-pill">Save profile</button>
        </form>
    </div></div></div>
    <div class="col-lg-6"><div class="card"><div class="card-body">
        <h2 class="h5">Password</h2>
        <form method="post" action="{{ route('settings.password') }}">@csrf @method('PUT')
            <label class="form-label small">Current password</label>
            <input type="password" name="current_password" class="form-control mb-2" autocomplete="current-password" required>
            <label class="form-label small">New password (10+ characters)</label>
            <input type="password" name="password" class="form-control mb-2" autocomplete="new-password" required>
            <label class="form-label small">Repeat the new password</label>
            <input type="password" name="password_confirmation" class="form-control mb-3" autocomplete="new-password" required>
            <button class="btn btn-outline-primary rounded-pill">Change password</button>
        </form>
    </div></div></div>
</div>
