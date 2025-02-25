@php
    $title = "Reporte pagos efectivo con destino caja V2";
    $title = str_replace(['*', ':', '/', '\\', '?', '[', ']'],'',$title);
    $title = substr($title, 0, 31);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>
    <style>
        html {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-spacing: 0;
            border: 1px solid black;
        }
        .celda {
            text-align: center;
            padding: 5px;
            border: 0.1px solid black;
        }
        th {
            padding: 5px;
            text-align: center;
            border-color: #0088cc;
            border: 0.1px solid black;
        }
        .title {
            font-weight: bold;
            padding: 5px;
            font-size: 20px !important;
            text-decoration: underline;
        }
        p > strong {
            margin-left: 5px;
            font-size: 12px;
        }
        thead {
            font-weight: bold;
            background: #0088cc;
            color: white;
            text-align: center;
        }
        .td-custom {
            line-height: 0.1em;
        }
        .width-custom {
            width: 50%
        }
    </style>
</head>
<body>
<div>
    <p align="center" class="title"><strong>Reporte Efectivo V2 - Punto de Venta</strong></p>
</div>
<div style="margin-top:20px; margin-bottom:20px;">
    <table>
        <tr>
            <td class="td-custom width-custom">
                <p><strong>Empresa: </strong>{{$data['company_name']}}</p>
            </td>
            <td class="td-custom">
                <p><strong>Fecha reporte: </strong>{{date('Y-m-d')}}</p>
            </td>
        </tr>
        <tr>
            <td class="td-custom">
                <p><strong>Ruc: </strong>{{$data['company_number']}}</p>
            </td>
            <td class="width-custom">
                <p><strong>Establecimiento: </strong>{{$data['establishment_address']}} - {{$data['establishment_department_description']}} - {{$data['establishment_district_description']}}
                </p>
            </td>
        </tr>
        <tr>
            <td class="td-custom">
                <p><strong>Vendedor: </strong>{{$data['cash_user_name']}}</p>
            </td>
            <td class="td-custom">
                <p><strong>Fecha y hora apertura: </strong>{{$data['cash_date_opening']}} {{$data['cash_time_opening']}}</p>
            </td>
        </tr>
        <tr>
            <td class="td-custom">
                <p><strong>Estado de caja: </strong>{{($data['cash_state']) ? 'Aperturada':'Cerrada'}}</p>
            </td>
            @if(!$data['cash_state'])
                <td class="td-custom">
                    <p><strong>Fecha y hora cierre: </strong>{{$data['cash_date_closed']}} {{$data['cash_time_closed']}}</p>
                </td>
            @endif
        </tr>
        
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        
        <tr>
            <td colspan="2" class="td-custom">
                <p><strong>Montos de operación en Soles: </strong></p>
            </td>
        </tr>
        <tr>
            <td class="td-custom">
                <p><strong>Saldo inicial efectivo:</strong>&nbsp; S/ {{$data['cash_beginning_balance']}}</p>
            </td>
            <td class="td-custom">
                <p><strong>Ingreso efectivo:</strong>&nbsp; S/ {{ $data['cash_income_pen'] }}</p>
            </td>
        </tr>
        <tr>
            <td class="td-custom">
                <p><strong>Saldo final efectivo:</strong>&nbsp; S/ {{ $data['balance_cash_pen'] }} </p>
            </td>
            <td class="td-custom">
                <p><strong>Egreso efectivo:</strong>&nbsp; S/ {{$data['cash_egress_pen']}} </p>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2" class="td-custom">
                <p><strong>Montos de operación en Dólares: </strong></p>
            </td>
        </tr>
        <tr>
            <td class="td-custom">
                <p><strong>Ingreso efectivo:</strong>&nbsp; $ {{ $data['cash_income_usd'] }}</p>
            </td>
            <td class="td-custom">
                <p><strong>Egreso efectivo:</strong>&nbsp; $ {{$data['cash_egress_usd']}} </p>
            </td>
        </tr>
        <tr>
            <td class="td-custom">
                <p><strong>Saldo final efectivo:</strong>&nbsp; $ {{ $data['balance_cash_usd'] }} </p>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    </table>
</div>

@if($data['payments_pen']->count() > 0)
    <div class="">
        <div class=" ">
            <div>
                <h3><b>Pagos en Soles</b></h3>
                <br>
            </div>
            <table class="">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Tipo transacción</th>
                    <th>Detalle de ingresos/gastos</th>
                    <th>Tipo documento</th>
                    <th>Documento</th>
                    <th>Fecha emisión</th>
                    <th>Cliente/Proveedor</th>
                    <th>N° Documento</th>
                    <th>Moneda</th>
                    <th>M.Pagado</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                @foreach($data['payments_pen'] as $key => $value) 
                    <tr>
                        <td class="celda">
                            {{ $loop->iteration }}
                        </td>
                        <td class="celda">
                            {{ $value['type_transaction_description'] }}
                        </td>
                        
                        <td class="celda">
                            {!! $value['items_description_html'] ?? '' !!}
                        </td>

                        <td class="celda">
                            {{ $value['document_type_description'] }}
                        </td>
                        <td class="celda">
                            {{ $value['number_full'] }}
                        </td>
                        <td class="celda">
                            {{ $value['date_of_issue'] }}
                        </td>
                        <td class="celda">
                            {{ $value['acquirer_name'] }}
                        </td>
                        <td class="celda">
                            {{ $value['acquirer_number'] }}
                        </td>
                        <td class="celda">
                            {{ $value['currency_type_id'] }}
                        </td>
                        <td class="celda">
                            {{ $value['payment'] }}
                        </td>
                        <td class="celda">
                            {{ $value['total'] }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="callout callout-info">
        <p>No se encontraron pagos en soles.</p>
    </div>
@endif

<br><br>

@if($data['payments_usd']->count() > 0) 
    <div class="">
        <div class=" ">
            <div>
                <h3><b>Pagos en Dólares</b></h3>
                <br>
            </div>
            <table class="">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Tipo transacción</th>
                    <th>Detalle de ingresos/gastos</th>
                    <th>Tipo documento</th>
                    <th>Documento</th>
                    <th>Fecha emisión</th>
                    <th>Cliente/Proveedor</th>
                    <th>N° Documento</th>
                    <th>Moneda</th>
                    <th>T.Pagado</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                @foreach($data['payments_usd'] as $key => $value)
                    <tr>
                        <td class="celda">
                            {{ $loop->iteration }}
                        </td>
                        <td class="celda">
                            {{ $value['type_transaction_description'] }}
                        </td>
                        
                        <td class="celda">
                            {!! $value['items_description_html'] ?? '' !!}
                        </td>

                        <td class="celda">
                            {{ $value['document_type_description'] }}
                        </td>
                        <td class="celda">
                            {{ $value['number_full'] }}
                        </td>
                        <td class="celda">
                            {{ $value['date_of_issue'] }}
                        </td>
                        <td class="celda">
                            {{ $value['acquirer_name'] }}
                        </td>
                        <td class="celda">
                            {{ $value['acquirer_number'] }}
                        </td>
                        <td class="celda">
                            {{ $value['currency_type_id'] }}
                        </td>
                        <td class="celda">
                            {{ $value['payment'] }}
                        </td>
                        <td class="celda">
                            {{ $value['total'] }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="callout callout-info">
        <p>No se encontraron pagos en dólares.</p>
    </div>
@endif
</body>
</html>
