@extends('admin.layouts.app')
@section('title', 'Registered Investors')
@section('page-title', 'Investor Relations & Capital Pool')

@section('content')
<div style="display:grid; grid-template-columns: 1fr 420px; gap:28px; align-items: start;">
    
    {{-- Left: Investors List --}}
    <div style="display:flex; flex-direction:column; gap:24px">
        <!-- Summary Banner -->
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); border-radius: 20px; padding: 28px; color: white; display: flex; justify-content: space-between; align-items: center; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);">
            <div style="position: absolute; right: -20px; bottom: -20px; font-size: 140px; opacity: 0.05; color: white;"><i class="bi bi-people-fill"></i></div>
            <div style="position: relative; z-index: 1;">
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; margin-bottom: 8px;">Investor Network</div>
                <h2 style="font-size: 28px; font-weight: 800; margin: 0; letter-spacing: -0.5px;">Corporate <span style="color: #60a5fa;">Funding Partners</span></h2>
                <p style="font-size: 13px; color: #94a3b8; margin-top: 8px; max-width: 450px;">Manage registered capital contributors, view active liabilities, and record new investment tranches.</p>
            </div>
            <div style="text-align: right; position: relative; z-index: 1;">
                <span class="badge" style="background: rgba(96, 165, 250, 0.2); color: #60a5fa; border: 1px solid rgba(96, 165, 250, 0.3); padding: 6px 12px; border-radius: 30px; font-weight: 700;">
                    <i class="bi bi-safe-fill"></i> {{ $investors->total() }} Active Profiles
                </span>
            </div>
        </div>

        <!-- Search Filter -->
        <div class="card" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 16px; padding: 15px 20px;">
            <form action="{{ route('admin.investments.investors.index') }}" method="GET" style="display:flex; gap:15px;">
                <div style="flex:1; position:relative">
                    <i class="bi bi-search" style="position:absolute; left:15px; top:12px; color:#94a3b8"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, ID number, or email..." style="width:100%; padding:10px 15px 10px 45px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px;">
                </div>
                <button type="submit" class="btn btn-primary" style="padding:10px 25px; font-weight:700">Filter</button>
                @if(request('search'))
                    <a href="{{ route('admin.investments.investors.index') }}" class="btn btn-light" style="padding:10px 20px;">Reset</a>
                @endif
            </form>
        </div>

        <div class="card" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border-radius: 16px;">
            <div class="card-hdr" style="padding: 20px 24px; background: #fff; border-bottom: 1px solid #f1f5f9;">
                <span class="card-title" style="font-size: 15px; font-weight: 800; color: #1e293b;">Investors Directory</span>
            </div>
            <div style="overflow-x:auto">
                <table class="dt" style="width:100%">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Partner</th>
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Type</th>
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Contact info</th>
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; text-align: center;">Investments</th>
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($investors as $i)
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#fcfdfe'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 18px 24px;">
                                <a href="{{ route('admin.investments.investors.show', $i->id) }}" style="font-weight: 800; color: #1e3a8a; font-size: 14px; text-decoration: none;">{{ $i->full_name }}</a>
                                <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;"><i class="bi bi-card-text"></i> ID: {{ $i->id_number }}</div>
                            </td>
                            <td style="padding: 18px 24px;">
                                @if($i->investor_type === 'MD')
                                    <span class="badge" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5; border: 1px solid rgba(79, 70, 229, 0.2); font-size: 10px; padding: 4px 8px;">MD (5% rate)</span>
                                @else
                                    <span class="badge" style="background: rgba(14, 116, 144, 0.1); color: #0e7490; border: 1px solid rgba(14, 116, 144, 0.2); font-size: 10px; padding: 4px 8px;">Public (1.5% rate)</span>
                                @endif
                            </td>
                            <td style="padding: 18px 24px; font-size:12.5px; color:#475569">
                                <div><i class="bi bi-telephone"></i> {{ $i->phone ?: 'N/A' }}</div>
                                <div style="font-size:11px; color:#94a3b8; margin-top:2px;"><i class="bi bi-envelope"></i> {{ $i->email ?: 'N/A' }}</div>
                            </td>
                            <td style="padding: 18px 24px; text-align: center; font-weight: 800; color:#1e293b;">
                                <span class="badge bg-light text-dark" style="font-size:11px; padding: 5px 10px;">{{ $i->investments_count }} tranches</span>
                            </td>
                            <td style="padding: 18px 24px; text-align: right;">
                                <a href="{{ route('admin.investments.investors.show', $i->id) }}" class="btn btn-sm btn-light" style="font-weight:700; color:#1e3a8a"><i class="bi bi-eye"></i> View Profile</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8; font-size: 13.5px;">
                                <i class="bi bi-inbox" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                                No investors found matching the search criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($investors->hasPages())
            <div style="padding: 20px 24px; border-top: 1px solid #f1f5f9;">
                {{ $investors->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Right: Register Form --}}
    <div>
        <div class="card" style="border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border-radius: 20px; position: sticky; top: 24px;">
            <div class="card-hdr" style="padding: 24px; background: #fff; border-bottom: 1px solid #f1f5f9;">
                <h3 style="font-size: 16px; font-weight: 800; color: #1e293b; margin: 0;"><i class="bi bi-person-plus-fill" style="color: #3b82f6;"></i> Onboard New Partner</h3>
                <p style="font-size: 12px; color: #94a3b8; margin: 4px 0 0 0;">Create an investor capital profile linked to an admin or borrower user.</p>
            </div>
            <div style="padding: 24px;">
                <form action="{{ route('admin.investments.investors.store') }}" method="POST" style="display:flex; flex-direction:column; gap:18px">
                    @csrf
                    
                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:6px">Associated Platform User</label>
                        <select name="user_id" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; background-color:#fff;">
                            <option value="">-- Choose User --</option>
                            @foreach(\App\Models\User::active()->get() as $u)
                                <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ strtoupper($u->role) }} - {{ $u->phone }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:6px">Classification Role</label>
                        <select name="investor_type" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; background-color:#fff;">
                            <option value="PUBLIC" {{ old('investor_type') == 'PUBLIC' ? 'selected' : '' }}>Public Investor (1.5%/month flat)</option>
                            <option value="MD" {{ old('investor_type') == 'MD' ? 'selected' : '' }}>Managing Director (5.0%/month flat)</option>
                        </select>
                    </div>

                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:6px">Legal Full Name</label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px;">
                    </div>

                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:6px">National ID / Passport Number</label>
                        <input type="text" name="id_number" value="{{ old('id_number') }}" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px;">
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px">
                        <div>
                            <label style="display:block; font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:6px">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:6px">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px;">
                        </div>
                    </div>

                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:6px">Registered Address</label>
                        <textarea name="address" rows="2" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; resize:none;">{{ old('address') }}</textarea>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px">
                        <div>
                            <label style="display:block; font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:6px">Bank Name</label>
                            <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="e.g. Standard Lesotho Bank" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:6px">Account Number</label>
                            <input type="text" name="account_number" value="{{ old('account_number') }}" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px;">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-weight:800; border-radius:8px; margin-top:10px;">
                        <i class="bi bi-check-circle-fill"></i> Save Investor Profile
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
