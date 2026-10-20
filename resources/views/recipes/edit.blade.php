@extends('layouts.app')

@section('title', 'Recipe – '.$menuItem->name)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <a href="{{ route('recipes.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Recipes</a>
            <h1 class="h3 fw-bold my-3">Recipe: {{ $menuItem->name }}</h1>
            <p class="text-muted">Quantity for <strong>one portion</strong>, in the ingredient's unit (e.g. 0.2 kg chicken).</p>

            @if ($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            @if ($ingredients->isEmpty())
                <div class="alert alert-warning">No ingredients yet. <a href="{{ route('ingredients.create') }}">Add ingredients</a> first.</div>
            @else
                @php
                    $rows = old('items', $menuItem->ingredients->map(fn ($i) => [
                        'ingredient_id' => $i->id, 'quantity_required' => (float) $i->pivot->quantity_required,
                    ])->values()->all());
                @endphp

                <form method="POST" action="{{ route('recipes.update', $menuItem) }}">
                    @csrf
                    @method('PUT')
                    <div class="card border-0 shadow-sm mb-3">
                        <table class="table align-middle mb-0">
                            <thead class="table-light"><tr><th>Ingredient</th><th style="width: 200px;">Quantity per portion</th><th></th></tr></thead>
                            <tbody id="rows">
                                @foreach ($rows as $i => $row)
                                    @include('recipes._row', ['i' => $i, 'row' => $row])
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr><td colspan="3">
                                    <button type="button" id="addRow" class="btn btn-sm btn-outline-secondary"><i class="bi bi-plus-lg"></i> Add ingredient</button>
                                </td></tr>
                            </tfoot>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-ec"><i class="bi bi-save"></i> Save Recipe</button>
                    <a href="{{ route('recipes.index') }}" class="btn btn-light">Cancel</a>
                </form>

                {{-- Empty row used by the "Add ingredient" button --}}
                <template id="rowTemplate">
                    @include('recipes._row', ['i' => '__INDEX__', 'row' => []])
                </template>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const tbody = document.getElementById('rows');
    let nextIndex = {{ count($rows ?? []) }};

    function refreshUnits() {
        tbody?.querySelectorAll('tr').forEach(row => {
            const opt = row.querySelector('select').selectedOptions[0];
            row.querySelector('.unit').textContent = opt && opt.dataset.unit ? opt.dataset.unit : '-';
        });
    }

    document.getElementById('addRow')?.addEventListener('click', () => {
        const html = document.getElementById('rowTemplate').innerHTML.replaceAll('__INDEX__', nextIndex++);
        tbody.insertAdjacentHTML('beforeend', html);
        refreshUnits();
    });
    tbody?.addEventListener('click', e => {
        const btn = e.target.closest('.remove');
        if (btn) btn.closest('tr').remove();
    });
    tbody?.addEventListener('change', refreshUnits);
    refreshUnits();
</script>
@endpush
