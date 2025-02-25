@extends('tenant.layouts.app')

@section('content')
    <tenant-sale-opportunities-form 
        :type-user="{{json_encode(Auth::user()->type)}}" 
        :id="{{json_encode($id)}}"
        :auth-user="{{json_encode(Auth::user()->getDataOnlyAuthUser())}}"
    >
    </tenant-sale-opportunities-form>
@endsection
