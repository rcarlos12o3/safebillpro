@extends('tenant.layouts.app')

@section('content')
    <tenant-contracts-form :type-user="{{json_encode(Auth::user()->type)}}"
     :id="{{json_encode($id)}}"
     :quotation-id="{{json_encode($quotationId)}}"
     :show-payments="{{json_encode($showPayments)}}"
     :auth-user="{{json_encode(Auth::user()->getDataOnlyAuthUser())}}"
    >
    </tenant-contracts-form>
@endsection
