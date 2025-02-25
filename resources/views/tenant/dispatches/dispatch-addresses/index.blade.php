@extends('tenant.layouts.app')

@section('content')

    <tenant-dispatch-addresses-index :type-user="{{json_encode(Auth::user()->type)}}"></tenant-dispatch-addresses-index>

@endsection
