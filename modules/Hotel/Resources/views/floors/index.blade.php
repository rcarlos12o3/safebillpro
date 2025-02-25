@extends('tenant.layouts.app')

@section('content')
    <tenant-hotel-floors 
        :floors='@json($floors)' 
        :establishments='@json($establishments)' 
        :user-type="'{{ $userType }}'"
        :establishment-id="{{$establishmentId}}"
    ></tenant-hotel-floors>
@endsection
