@extends('item::layouts.web')

@section('content')

    <tenant-item-detail-index
        :record-id="{{json_encode($item_id)}}"
    >
    </tenant-item-detail-index>

@endsection