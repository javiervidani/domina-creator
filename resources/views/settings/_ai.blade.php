<div class="small text-muted mb-3">Your own keys, for the AI suggestions that arrive in a later phase. A key is stored encrypted and shown only as its last four characters. It cannot be read back, only replaced, tested or removed.</div>
<div class="row g-3">
@php($ordered = collect($providers)->sortBy(fn ($p, $id) => $keys->has($id) ? 0 : 1))
@foreach ($ordered as $id => $p)
    @php($k = $keys->get($id))
    <div class="col-lg-6"><div class="card h-100"><div class="card-body">
        @unless ($k)<details class="ai-add"><summary class="d-flex align-items-center gap-2"><strong>{{ $p['label'] }}</strong><span class="pill ms-auto">Add key</span></summary><div class="mt-3">@endunless
        @if ($k)<div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
            <strong>{{ $p['label'] }}</strong>
                <span class="pill {{ $k->enabled ? 'good' : '' }}">{{ $k->enabled ? 'On' : 'Off' }}</span>
                @if ($k->is_default)<span class="pill blue">Default</span>@endif
                @if ($k->last_test_ok === true)<span class="pill good">Key works</span>
                @elseif ($k->last_test_ok === false)<span class="pill bad">Not accepted</span>
                @else<span class="pill warn">Not tested</span>@endif
        </div>@endif
        @if ($k && $k->last_test_ok === false)<div class="small text-danger mb-2">{{ $k->last_test_error }}</div>@endif

        <form method="post" action="{{ route('ai.store') }}" autocomplete="off">@csrf
            <input type="hidden" name="provider" value="{{ $id }}">
            <label class="form-label small">API key {{ $k ? '(saved: '.$k->masked().', leave empty to keep)' : '' }}</label>
            <input type="password" name="api_key" class="form-control mb-2" placeholder="{{ $k ? '' : 'Paste your key' }}" autocomplete="off">
            <label class="form-label small">Default model</label>
            <input name="default_model" class="form-control mb-1" value="{{ $k?->default_model ?? $p['model'] }}" list="models-{{ $id }}" autocomplete="off" placeholder="Pick from the list or type">
            @if ($k && $k->models)<datalist id="models-{{ $id }}">@foreach ($k->models as $m)<option value="{{ $m }}">@endforeach</datalist>@endif
            <div class="small text-muted mb-2">@if ($k && $k->models){{ count($k->models) }} models your key can use (loaded {{ $k->models_at?->diffForHumans() }}). Clear the box to see them all.
                @elseif ($k)Press <b>Test key</b> to load the list of models this key can use.@endif</div>
            <button class="btn btn-sm btn-primary rounded-pill">{{ $k ? 'Save' : 'Add key' }}</button>
        </form>

        @if ($k)
        <div class="d-flex gap-2 flex-wrap mt-2">
            <form method="post" action="{{ route('ai.test', $k) }}">@csrf<button class="btn btn-sm btn-outline-primary rounded-pill">Test key</button></form>
            <form method="post" action="{{ route('ai.toggle', $k) }}">@csrf<button class="btn btn-sm btn-outline-secondary rounded-pill">{{ $k->enabled ? 'Turn off' : 'Turn on' }}</button></form>
            @unless ($k->is_default)
            <form method="post" action="{{ route('ai.default', $k) }}">@csrf<button class="btn btn-sm btn-outline-secondary rounded-pill">Make default</button></form>
            @endunless
            <form method="post" action="{{ route('ai.destroy', $k) }}" onsubmit="return confirm('Remove this key?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger rounded-pill">Remove</button></form>
        </div>
        @endif
        @unless ($k)</div></details>@endunless
    </div></div></div>
@endforeach
</div>
<div class="small text-muted mt-2">The model list comes from the provider when you press Test key. Pick one, or type a name.</div>
