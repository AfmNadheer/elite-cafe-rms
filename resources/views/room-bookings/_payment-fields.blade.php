{{-- Cash or card machine. Expects $prefix (unique id prefix). --}}
<div class="btn-group w-100 mb-2" role="group">
    <input type="radio" class="btn-check pay-method" name="method" id="{{ $prefix }}cash" value="cash" @checked(old('method', 'cash') === 'cash') data-box="{{ $prefix }}cardBox">
    <label class="btn btn-outline-success btn-sm" for="{{ $prefix }}cash"><i class="bi bi-cash"></i> Cash</label>
    <input type="radio" class="btn-check pay-method" name="method" id="{{ $prefix }}card" value="card" @checked(old('method') === 'card') data-box="{{ $prefix }}cardBox">
    <label class="btn btn-outline-primary btn-sm" for="{{ $prefix }}card"><i class="bi bi-credit-card"></i> Card machine</label>
</div>
<div id="{{ $prefix }}cardBox" class="row g-2 mb-2" style="display: none;">
    <div class="col-12">
        <select name="card_type" class="form-select form-select-sm @error('card_type') is-invalid @enderror" aria-label="Card type">
            <option value="">Card type…</option>
            @foreach (\App\Models\Payment::CARD_TYPES as $key => $label)
                <option value="{{ $key }}" @selected(old('card_type') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-6"><input type="text" name="card_last4" value="{{ old('card_last4') }}" maxlength="4" inputmode="numeric" class="form-control form-control-sm @error('card_last4') is-invalid @enderror" placeholder="Last 4 digits" aria-label="Last 4 digits"></div>
    <div class="col-6"><input type="text" name="approval_code" value="{{ old('approval_code') }}" maxlength="12" class="form-control form-control-sm text-uppercase @error('approval_code') is-invalid @enderror" placeholder="Approval code" aria-label="Approval code"></div>
    @foreach (['card_type', 'card_last4', 'approval_code'] as $f)
        @error($f)<div class="col-12 text-danger small">{{ $message }}</div>@enderror
    @endforeach
</div>
