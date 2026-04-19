@extends('admin.layouts.app')
@section('title', $bank->name . ' - Branches')
@section('bc')
<a href="{{ route('admin.dashboard') }}">Home</a> / <a href="{{ route('admin.banks.index') }}">Banks</a> / {{ $bank->name }}
@endsection
@section('content')
<div class="flex jb aic mb6">
    <div>
        <h1 style="font-size:20px;font-weight:800;color:var(--pd)">{{ $bank->name }} Branches</h1>
        <div class="muted" style="font-size:13px;margin-top:2px">Manage branch names and automation codes for this bank.</div>
    </div>
    <button class="btn btn-p" onclick="openModal('addBranchModal')">
        <i class="bi bi-plus-lg"></i> Add New Branch
    </button>
</div>

<div class="card">
    <div class="card-body" style="padding:0">
        <table class="dt">
            <thead>
                <tr>
                    <th>Branch Name</th>
                    <th>Branch Code</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branches as $b)
                <tr>
                    <td style="font-weight:700;color:var(--p)">{{ $b->name }}</td>
                    <td><code>{{ $b->code ?: '—' }}</code></td>
                    <td style="text-align:right">
                        <div class="flex aic" style="justify-content:flex-end;gap:8px">
                            <button class="tbtn" onclick="editBranch({{ $b->id }}, '{{ addslashes($b->name) }}', '{{ $b->code }}')" title="Edit Branch">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <form action="{{ route('admin.banks.branches.destroy', [$bank, $b]) }}" method="POST" onsubmit="return confirm('Delete this branch?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="tbtn" style="color:var(--err)" title="Delete">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Add Branch Modal --}}
<div class="mo" id="addBranchModal">
    <div class="mb">
        <form action="{{ route('admin.banks.branches.store', $bank) }}" method="POST">
            @csrf
            <div class="mh">
                <div class="mt">Add New Branch</div>
                <button type="button" class="mc" onclick="closeModal('addBranchModal')">&times;</button>
            </div>
            <div class="mbody">
                <div class="fg">
                    <label class="fl">Branch Name</label>
                    <input type="text" name="name" class="fc" placeholder="e.g. Maseru Kingsway" required>
                </div>
                <div class="fg">
                    <label class="fl">Branch Code</label>
                    <input type="text" name="code" class="fc" placeholder="e.g. 390161">
                </div>
            </div>
            <div class="mf">
                <button type="button" class="btn btn-o" onclick="closeModal('addBranchModal')">Cancel</button>
                <button type="submit" class="btn btn-p">Add Branch</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Branch Modal --}}
<div class="mo" id="editBranchModal">
    <div class="mb">
        <form id="editBranchForm" method="POST">
            @csrf @method('PUT')
            <div class="mh">
                <div class="mt">Edit Branch</div>
                <button type="button" class="mc" onclick="closeModal('editBranchModal')">&times;</button>
            </div>
            <div class="mbody">
                <div class="fg">
                    <label class="fl">Branch Name</label>
                    <input type="text" name="name" id="edit_branch_name" class="fc" required>
                </div>
                <div class="fg">
                    <label class="fl">Branch Code</label>
                    <input type="text" name="code" id="edit_branch_code" class="fc">
                </div>
            </div>
            <div class="mf">
                <button type="button" class="btn btn-o" onclick="closeModal('editBranchModal')">Cancel</button>
                <button type="submit" class="btn btn-p">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function editBranch(id, name, code) {
        document.getElementById('editBranchForm').action = "/admin/banks/{{ $bank->id }}/branches/" + id;
        document.getElementById('edit_branch_name').value = name;
        document.getElementById('edit_branch_code').value = code;
        openModal('editBranchModal');
    }
</script>
@endpush
