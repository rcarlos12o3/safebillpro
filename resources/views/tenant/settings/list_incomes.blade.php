@extends('tenant.layouts.app')

@section('content')
<div class="page-header pr-0">
    <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
    <ol class="breadcrumbs">
        <li class="active"><span> Motivos de ingresos / Gastos </span></li>
    </ol>
</div>
<div class="row tab-content-default row-new bg-transparent">
    <div class="col-12 ui-sortable">
        <tenant-expense-reasons-index :type-user="{{json_encode(Auth::user()->type)}}" ></tenant-expense-reasons-index>
    </div>
    <div class="col-12 ui-sortable">
        <tenant-income-reasons-index :type-user="{{json_encode(Auth::user()->type)}}" ></tenant-income-reasons-index>
    </div>
</div>
@endsection
