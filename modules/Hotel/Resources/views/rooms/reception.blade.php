@extends('tenant.layouts.app')

@section('content')
    <tenant-hotel-reception 
        :floors='@json($floors)' 
        :room-status='@json($roomStatus)' 
        :rooms='@json($rooms)'
        :user-type="'{{ $userType }}'"
        :establishment-id="{{$establishmentId}}"
        :establishments='@json($establishments)'
    ></tenant-hotel-reception>
@endsection
