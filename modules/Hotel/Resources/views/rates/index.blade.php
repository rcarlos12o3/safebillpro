@extends('tenant.layouts.app')

@section('content')
    <tenant-hotel-rates 
        :rates='@json($rates)'
        :establishments='@json($establishments)' 
        :user-type="'{{ $userType }}'"
        :establishment-id="{{$establishmentId}}"
    ></tenant-hotel-rates>
@endsection
