@extends('tenant.layouts.app')

@section('content')
<div class="page-header pr-0">
    <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
    <ol class="breadcrumbs">
        <li class="active"><span> Tipos de comprobantes INGRESOS Y GASTOS </span></li>
    </ol>
</div>
<div class="row tab-content-default row-new bg-transparent">
    <div class="col-12 ui-sortable">
        <tenant-income-types-index :type-user="{{json_encode(Auth::user()->type)}}"></tenant-income-types-index>
    </div>
    <div class="col-12 ui-sortable">
        <tenant-expense-types-index :type-user="{{json_encode(Auth::user()->type)}}" ></tenant-expense-types-index>
    </div>
</div>
@endsection
