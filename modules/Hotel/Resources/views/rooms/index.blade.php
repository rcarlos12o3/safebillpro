@extends('tenant.layouts.app')

@section('content')
    <tenant-hotel-rooms 
        :floors='@json($floors)' 
        :room-status='@json($roomStatus)' 
        :categories='@json($categories)' 
        :rooms='@json($rooms)'
        :establishments='@json($establishments)' 
        :user-type="'{{ $userType }}'"
        :establishment-id="{{$establishmentId}}"
    ></tenant-hotel-rooms>
@endsection
