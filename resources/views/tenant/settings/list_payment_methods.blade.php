@extends('tenant.layouts.app')

@section('content')
<div class="page-header pr-0">
    <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
    <ol class="breadcrumbs">
        <li class="active"><span> Métodos de pago - ingreso / gastos </span></li>
    </ol>
</div>
<div class="row tab-content-default row-new bg-transparent">
    <div class="col-12 ui-sortable">
        <tenant-expense-method-types-index :type-user="{{json_encode(Auth::user()->type)}}">
        </tenant-expense-method-types-index>
    </div>
    <div class="col-12 ui-sortable">
        <tenant-payment-method-types-index :type-user="{{json_encode(Auth::user()->type)}}" ></tenant-payment-method-types-index>
    </div>
</div>
@endsection
