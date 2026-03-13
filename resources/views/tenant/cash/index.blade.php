@extends('tenant.layouts.app')

@section('content')

    <cash-index
        :type-user="{{json_encode(Auth::user()->type)}}"
        :current-user="{{json_encode($user)}}"
        :establishments="{{json_encode($establishments)}}"
    ></cash-index>

@endsection
