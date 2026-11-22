{{-- The "add a target" form. Needs $accounts and the $at helper; used by Accounts and the setup wizard. --}}
@php($from = $accounts->whereIn('platform', ['instagram', 'youtube'])->values())
@php($to = $accounts->whereIn('platform', \App\Publishing\Publisher::SUPPORTED)->values())
@if ($from->isEmpty() || $to->isEmpty())
    <div class="small text-muted">To add a target, connect an account to import from and one to publish to (Telegram).</div>
@else
<form method="post" action="{{ route('targets.store') }}" class="target-add">@csrf
    <div><label class="form-label small mb-1">From</label>
        <select name="source_account_id" class="form-select form-select-sm" dir="auto">@foreach ($from as $a)<option value="{{ $a->id }}" @selected($loop->first)>{{ $at($a) }}</option>@endforeach</select></div>
    <div><label class="form-label small mb-1">To</label>
                <select name="target_account_id" class="form-select form-select-sm" dir="auto">@foreach ($to as $a)<option value="{{ $a->id }}" @selected($loop->last)>{{ $at($a) }}</option>@endforeach</select></div>
    <button class="btn btn-sm btn-outline-primary rounded-pill">Add target (review first)</button>
</form>
@endif
