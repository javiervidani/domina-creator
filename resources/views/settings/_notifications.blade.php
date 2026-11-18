<div class="card"><div class="card-body">
    <h2 class="h5">Email me when</h2>
    <form method="post" action="{{ route('settings.notifications') }}">@csrf @method('PATCH')
        @foreach ($notify as $key => $item)
            <div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="{{ $key }}" name="{{ $key }}" value="1" @checked($item['on'])>
                <label class="form-check-label" for="{{ $key }}">{{ $item['label'] }}</label></div>
        @endforeach
        <button class="btn btn-primary rounded-pill mt-2">Save choices</button>
    </form>
    <div class="small text-muted mt-3">Your choices are saved. Sending these emails is not switched on yet, so nothing is mailed today.</div>
</div></div>
