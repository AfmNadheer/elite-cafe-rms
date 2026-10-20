<tr>
    <td>
        <select name="items[{{ $i }}][ingredient_id]" class="form-select" required>
            <option value="">Select…</option>
            @foreach ($ingredients as $ing)
                <option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}" @selected(($row['ingredient_id'] ?? '') == $ing->id)>{{ $ing->name }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <div class="input-group">
            <input type="number" step="0.001" min="0.001" name="items[{{ $i }}][quantity_required]" value="{{ $row['quantity_required'] ?? '' }}" class="form-control" required>
            <span class="input-group-text unit">-</span>
        </div>
    </td>
    <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove" aria-label="Remove"><i class="bi bi-x-lg"></i></button></td>
</tr>
