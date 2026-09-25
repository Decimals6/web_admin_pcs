@extends('layouts.admin')

@section('content')
<h3>Edit User</h3>

<form method="POST" action="{{ route('users.update', $user->id) }}">
    @csrf 
    @method('PUT')

    <div class="form-group">
        <label>Nama</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
    </div>

    <div class="form-group">
        <label>Password (kosongkan jika tidak diubah)</label>
        <input type="password" name="password" class="form-control">
    </div>

    <!-- Tambahan Status User -->
    <div class="form-group">
        <label>Status Akun</label>
        <select name="is_active" class="form-control">
            <option value="1" {{ old('is_active', $user->is_active) ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ !old('is_active', $user->is_active) ? 'selected' : '' }}>Nonaktif</option>
        </select>
    </div>

    <button class="btn btn-primary">Update</button>
</form>
@endsection