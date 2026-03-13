@extends('tenant.layouts.app')

@section('content')

    <tenant-sale-notes-index
        :soap-company="{{ json_encode($soap_company) }}"
        :type-user="{{ json_encode(auth()->user()->type) }}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
        :current-user="{{ json_encode($user) }}"
        :establishments="{{ json_encode($establishments) }}"
    ></tenant-sale-notes-index>

@endsection
