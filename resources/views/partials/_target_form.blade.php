{{-- The "add a target" form. Needs $accounts and the $at helper; used by Accounts and the setup wizard. --}}
<form method="post" action="{{ route('targets.store') }}" class="target-add">@csrf
    <div><label class="form-label small mb-1">From</label>
        <select name="source_account_id" class="form-select form-select-sm" dir="auto">@foreach ($accounts as $a)<option value="{{ $a->id }}" @selected($loop->first)>{{ $at($a) }}</option>@endforeach</select></div>
    <div><label class="form-label small mb-1">To</label>
        {{-- The last account by default, so "From" and "To" start as different accounts. --}}
        <select name="target_account_id" class="form-select form-select-sm" dir="auto">@foreach ($accounts as $a)<option value="{{ $a->id }}" @selected($loop->last)>{{ $at($a) }}</option>@endforeach</select></div>
    <button class="btn btn-sm btn-outline-primary rounded-pill">Add target (review first)</button>
</form>
