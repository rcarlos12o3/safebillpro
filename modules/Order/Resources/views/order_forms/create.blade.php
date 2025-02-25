@extends('tenant.layouts.app')

@section('content')
    <tenant-dispatches-create
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
        :order_form="{{ json_encode($orderForm) }}"
    ></tenant-dispatches-create>
@endsection
