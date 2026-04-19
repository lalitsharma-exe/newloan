@extends('admin.layouts.app')
@section('title', 'Manage Banks')
@section('bc')
<a href="{{ route('admin.dashboard') }}">Home</a> / Manage Banks
@endsection
@section('content')
<div class="flex jb aic mb6">
    <div>
        <h1 style="font-size:20px;font-weight:800;color:var(--pd)">Manage Banks</h1>
        <div class="muted" style="font-size:13px;margin-top:2px">Configure valid banking options for application forms.</div>
    </div>
    <button class="btn btn-p" onclick="openModal('addBankModal')">
        <i class="bi bi-plus-lg"></i> Add New Bank
    </button>
</div>

<div class="card">
    <div class="card-body" style="padding:0">
        <table class="dt">
            <thead>
                <tr>
                    <th>Bank Name</th>
                    <th>Branches</th>
                    <th>Created At</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($banks as $b)
                <tr>
                    <td style="font-weight:700;color:var(--p)">{{ $b->name }}</td>
                    <td>
                        <span class="badge bi">{{ $b->branches_count }} branches</span>
                    </td>
                    <td class="muted">{{ $b->created_at->format('d M Y') }}</td>
                    <td style="text-align:right">
                        <div class="flex aic jb" style="justify-content:flex-end;gap:8px">
                            <a href="{{ route('admin.banks.branches', $b) }}" class="btn btn-o btn-sm">
                                <i class="bi bi-list-task"></i> Branches
                            </a>
                            <button class="tbtn" onclick="editBank({{ $b->id }}, '{{ addslashes($b->name) }}')" title="Edit Bank">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <form action="{{ route('admin.banks.destroy', $b) }}" method="POST" onsubmit="return confirm('Delete this bank and all its branches?')">
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

{{-- Add Bank Modal --}}
<div class="mo" id="addBankModal">
    <div class="mb">
        <form action="{{ route('admin.banks.store') }}" method="POST">
            @csrf
            <div class="mh">
                <div class="mt">Add New Bank</div>
                <button type="button" class="mc" onclick="closeModal('addBankModal')">&times;</button>
            </div>
            <div class="mbody">
                <div class="fg">
                    <label class="fl">Bank Name</label>
                    <input type="text" name="name" class="fc" placeholder="e.g. Nedbank Lesotho" required>
                </div>
            </div>
            <div class="mf">
                <button type="button" class="btn btn-o" onclick="closeModal('addBankModal')">Cancel</button>
                <button type="submit" class="btn btn-p">Add Bank</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Bank Modal --}}
<div class="mo" id="editBankModal">
    <div class="mb">
        <form id="editBankForm" method="POST">
            @csrf @method('PUT')
            <div class="mh">
                <div class="mt">Edit Bank</div>
                <button type="button" class="mc" onclick="closeModal('editBankModal')">&times;</button>
            </div>
            <div class="mbody">
                <div class="fg">
                    <label class="fl">Bank Name</label>
                    <input type="text" name="name" id="edit_bank_name" class="fc" required>
                </div>
            </div>
            <div class="mf">
                <button type="button" class="btn btn-o" onclick="closeModal('editBankModal')">Cancel</button>
                <button type="submit" class="btn btn-p">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function editBank(id, name) {
        document.getElementById('editBankForm').action = "/admin/banks/" + id;
        document.getElementById('edit_bank_name').value = name;
        openModal('editBankModal');
    }
</script>
@endpush
