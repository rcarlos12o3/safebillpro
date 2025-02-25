@extends('tenant.layouts.app')

@section('content')
    <tenant-hotel-categories 
        :categories='@json($categories)'
        :establishments='@json($establishments)' 
        :user-type="'{{ $userType }}'"
        :establishment-id="{{$establishmentId}}"
    ></tenant-hotel-categories>
@endsection
