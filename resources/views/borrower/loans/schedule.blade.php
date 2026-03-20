@extends('borrower.layouts.app')
@section('title','Schedule — '.$loan->loan_number)
@section('content')
<div style="font-size:20px;font-weight:800;margin-bottom:18px">Repayment Schedule — {{ $loan->loan_number }}</div>
@include('borrower.loans.show')
@endsection
