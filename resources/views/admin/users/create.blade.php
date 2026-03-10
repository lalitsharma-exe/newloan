@extends('admin.layouts.app')
@section('title','Create User')
@section('page-title','Create User')
@section('content')
<div style="max-width:600px">
<div class="card">
    <div class="card-header"><span class="card-title">New User</span></div>
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div class="card-body">
            <div class="grid grid-2" style="gap:16px">
                <div class="form-group"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
                <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
                <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone') }}"></div>
                <div class="form-group"><label class="form-label">Role *</label><select name="role" class="form-control" required><option value="">Select Role</option><option value="admin">Admin</option><option value="loan_officer">Loan Officer</option><option value="borrower">Borrower</option></select></div>
                <div class="form-group"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Confirm Password *</label><input type="password" name="password_confirmation" class="form-control" required></div>
            </div>
            <div class="form-group"><label style="display:flex;align-items:center;gap:8px;cursor:pointer"><input type="checkbox" name="is_active" value="1" checked> <span>Active</span></label></div>
        </div>
        <div style="padding:16px 24px;border-top:1px solid #e2e8f0;display:flex;gap:10px;justify-content:flex-end">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Create User</button>
        </div>
    </form>
</div>
</div>
@endsection
