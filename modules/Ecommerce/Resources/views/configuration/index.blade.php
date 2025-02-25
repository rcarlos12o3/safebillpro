@extends('tenant.layouts.app')

@section('content')
<div class="page-header pr-0">
    <h2><a href="/ecommerce/configuration">
    <i class="fas fa-cogs"></i>
    </a></h2>
    <ol class="breadcrumbs">
        <li class="active"><span> Configuración </span></li>
    </ol>
</div>
<div class="row tab-content-default row-new bg-transparent">
    <tenant-ecommerce-configuration-info></tenant-ecommerce-configuration-info>
    <tenant-ecommerce-configuration-culqi></tenant-ecommerce-configuration-culqi>
    <tenant-ecommerce-configuration-paypal></tenant-ecommerce-configuration-paypal>

    <tenant-ecommerce-configuration-logo></tenant-ecommerce-configuration-logo>
    <tenant-ecommerce-configuration-social></tenant-ecommerce-configuration-social>
    <tenant-ecommerce-configuration-tag></tenant-ecommerce-configuration-tag>
    <tenant-ecommerce-configuration-links></tenant-ecommerce-configuration-links>

</div>
@endsection

