@extends('admin.layouts.app')
@section('title','Submission Detail')
@section('page-title','Submission Detail')
@section('bc','<a href="'.route('admin.credit.index').'">Credit Bureau</a> / Submission')
@section('content')
<div class="card">
  <div class="card-hdr">
    <span class="card-title">Submission #{{ $id }}</span>
    <a href="{{ route('admin.credit.monthly-submissions') }}" class="btn btn-o btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
  </div>
  <div style="padding:40px;text-align:center;color:var(--muted)">
    <i class="bi bi-file-earmark-check" style="font-size:48px;opacity:.3;display:block;margin-bottom:12px"></i>
    <div style="font-size:13px">Submission detail view — coming soon.</div>
  </div>
</div>
@endsection
