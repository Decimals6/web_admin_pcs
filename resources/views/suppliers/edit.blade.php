<div class="form-group">
    <label>Supplier</label>
    <select name="supplier_id" class="form-control">
        <option value="">-- Pilih Supplier --</option>
        @foreach($suppliers as $s)
            <option value="{{ $s->id }}"
                {{ old('supplier_id', $barang->supplier_id ?? '') == $s->id ? 'selected' : '' }}>
                {{ $s->nama_supplier }}
            </option>
        @endforeach
    </select>
</div>
