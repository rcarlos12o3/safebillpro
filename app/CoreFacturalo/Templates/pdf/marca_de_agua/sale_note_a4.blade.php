@php
    use Modules\Template\Helpers\TemplatePdf;

    $establishment = $document->establishment;
    $customer = $document->customer;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');

    $left =  ($document->series) ? $document->series : $document->prefix;
    $tittle = $left.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $payments = $document->payments;
    // $accounts = \App\Models\Tenant\BankAccount::all();
    $accounts = (new TemplatePdf)->getBankAccountsForPdf($document->establishment_id);

    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

    $configurationInPdf= App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationInPdf();
    
        $totalProductos = count($document->items);
        $totalFilas = 6 + $totalProductos;

    // if (!empty($configurationInPdf->show_bank_accounts_in_pdf) &&
    //     in_array($document->document_type->id, ['01', '03'])) {
    //     $totalFilas += count($accounts);
    // }

    if ($document->payment_method_type_id) {
        $totalFilas++;
    }

    // if ($document->payment_condition_id === '01' && $payments->count()) {
    //     $totalFilas += $payments->count();
    // } else {
    //     $totalFilas += count($document->fee);
    // }

    $allowed = $totalFilas;
    if ($allowed>26){
        $allowed_items = 70 - $totalFilas;
    }elseif($totalProductos>=20){
        $allowed_items = 0;
    }
    elseif($allowed<=26){
        $allowed_items = 70 - $totalProductos;
    }

    $quantity_items = $document->items()->count();
    $cycle_items = $allowed_items - ($quantity_items * 3);
    $total_weight = 0;


    $type = App\CoreFacturalo\Helpers\Template\TemplateHelper::getTypeSoap();
@endphp
<html>
<head>
    {{--<title>{{ $tittle }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
@if($document->state_type->id == '11')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
        <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
@else
<div class="item_watermark" style="position: absolute; text-align: center; top:42%;">
    <img style="width: 100%" height="200px" src="data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$company->name}}" alt="anulado" class="" style="opacity: 0.1;width: 95%">
</div>
@endif
<table class="full-width">
    <tr>
        @if($company->logo)
            <td width="20%">
                <div class="company_logo_box">
                    <img src="data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$company->name}}" class="company_logo" style="max-width: 150px;">
                </div>
            </td>
        @else
            <td width="20%">
            </td>
        @endif
        <td width="50%" class="pl-3">
            <div class="text-left">
                <h4 class="">{{ $company->name }}</h4>
                <h5>{{ 'RUC '.$company->number }}</h5>
                <h6 style="text-transform: uppercase;">
                    {{ ($establishment->address !== '-')? $establishment->address : '' }}
                    {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                    {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                    {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                </h6>
                <h6>{{ ($establishment->email !== '-')? $establishment->email : '' }}</h6>
                <h6>{{ ($establishment->telephone !== '-')? $establishment->telephone : '' }}</h6>
            </div>
        </td>
        <td width="30%" class="border-box py-4 px-2 text-center">
            <h5 class="text-center font-bold">NOTA DE VENTA</h5>
            <h3 class="text-center font-bold">{{ $tittle }}</h3>
        </td>
    </tr>
</table>
<table class="full-width mt-3">
    <tr>
        <td width="47%" class="border-box pl-3 align-top">
            <table class="full-width">
                <tr>
                    <td class="font-sm" width="80px">
                        <strong>Cliente</strong>
                        <td class="font-sm" width="8px">:</td>
                        <td class="font-sm">
                            {{ $customer->name }}
                        </td>
                    </td>
                </tr>
                <tr>
                        <td class="font-sm" width="80px">
                            <strong>{{$customer->identity_document_type->description}}</strong>
                            <td class="font-sm" width="8px">:</td>
                            <td class="font-sm">
                                {{$customer->number}}
                            </td>
                        </td>
                </tr>
                @if ($customer->address !== '')
                <tr>
                    <td class="font-sm align-top" width="80px">
                        <strong>Dirección</strong>
                    </td>
                    <td class="font-sm align-top" width="8px">:</td>
                    <td colspan="3">
                        {{ strtoupper($customer->address) }}
                        {{ ($customer->district_id !== '-')? ', '.strtoupper($customer->district->description) : '' }}
                        {{ ($customer->province_id !== '-')? ', '.strtoupper($customer->province->description) : '' }}
                        {{ ($customer->department_id !== '-')? '- '.strtoupper($customer->department->description) : '' }}
                    </td>
                </tr>
                @endif
                <tr>
                    <td class="font-sm align-top" width="80px">
                        <strong>Teléfono:</strong>
                    </td>
                    <td class="font-sm align-top" width="8px">:</td>
                    <td class="font-sm">
                        {{ $customer->telephone }}
                    </td>
                </tr>
            </table>
        </td>
        <td width="3%"></td>
        <td width="50%" class="border-box pl-3 align-top">
            <table class="full-width">
                <tr>
                    <tr>
                        <td class="font-sm" width="90px">
                            <strong>Fecha Emisión</strong>
                        </td>
                        <td class="font-sm" width="8px">:</td>
                        <td class="font-sm">
                           {{$document->date_of_issue->format('Y-m-d')}} / {{ $document->time_of_issue }}
                        </td>
                    </tr>
                    <tr>
                        @if ($document->due_date)
                            <td class="font-sm" width="65px">
                                <strong>Fecha Vencimiento</strong>
                            </td>
                            <td class="font-sm" width="8px">:</td>
                            <td>{{ $document->getFormatDueDate() }}</td>
                        @endif
                    </tr>
                </tr>
                <tr>
                    @if ($document->total_canceled)
                    <td class="font-sm align-top" width="50px">
                        <strong>Estado</strong>
                        <td class="font-sm align-top" width="8px">:</td>
                        <td colspan="3">CANCELADO</td>
                    </td>

                    @else
                    <td class="font-sm align-top" width="50px">
                        <strong>Estado</strong>
                        <td class="font-sm align-top" width="8px">:</td>
                        <td colspan="3">PENDIENTE DE PAGO</td>
                    </td>
                    @endif
                    @if ($document->plate_number !== null)
                        <td>
                            <tr>
                                <td width="15%">N° Placa:</td>
                                <td width="85%">{{ $document->plate_number }}</td>
                            </tr>
                        </td>
                    @endif
                </tr>
                <tr>
                    @if ($document->observation)
                    <td class="font-sm align-top" width="50px">
                        <strong>Observación</strong>
                        <td class="font-sm align-top" width="8px">:</td>
                        <td colspan="3">{{ $document->observation }}</td>
                    </td>
                    @endif
                    @if ($document->reference_data)
                        <td class="font-sm align-top" width="50px">
                            <strong>D. Referencia</strong>
                            <td class="font-sm align-top" width="8px">:</td>
                            <td colspan="3">{{ $document->reference_data }}</td>
                        </td>
                    @endif
                </tr>
                <tr>
                    @if ($document->purchase_order)
                        <td class="font-sm align-top" width="50px">
                            <strong>Orden de compra</strong>
                            <td class="font-sm align-top" width="8px">:</td>
                            <td colspan="3">{{ $document->purchase_order }}</td>
                        </td>
                    @endif

                </tr>

            </table>
        </td>
    </tr>
    {{-- <tr>
        <td width="15%">Cliente:</td>
        <td width="45%">{{ $customer->name }}</td>
        <td width="25%">Fecha de emisión:</td>
        <td>{{$document->date_of_issue->format('Y-m-d')}} / {{ $document->time_of_issue }}</td>
    </tr> --}}
    {{-- <tr>
        <td>{{ $customer->identity_document_type->description }}:</td>
        <td>{{ $customer->number }}</td>

        @if ($document->due_date)
            <td class="align-top">Fecha Vencimiento:</td>
            <td>{{ $document->getFormatDueDate() }}</td>
        @endif

    </tr> --}}
    {{-- @if ($customer->address !== '')
    <tr>
        <td class="align-top">Dirección:</td>
        <td colspan="3">
            {{ strtoupper($customer->address) }}
            {{ ($customer->district_id !== '-')? ', '.strtoupper($customer->district->description) : '' }}
            {{ ($customer->province_id !== '-')? ', '.strtoupper($customer->province->description) : '' }}
            {{ ($customer->department_id !== '-')? '- '.strtoupper($customer->department->description) : '' }}
        </td>
    </tr>
    @endif --}}
    {{-- <tr>
        <td>Teléfono:</td>
        <td>{{ $customer->telephone }}</td>
        @if(isset($configurationInPdf) && $configurationInPdf->show_seller_in_pdf)
            <td>Vendedor:</td>
            <td> @if($document->seller_id != 0){{$document->seller->name }} @else {{ $document->user->name }} @endif</td>
        @endif
    </tr> --}}
    {{-- @if ($document->plate_number !== null)
    <tr>
        <td width="15%">N° Placa:</td>
        <td width="85%">{{ $document->plate_number }}</td>
    </tr>
    @endif --}}
    {{-- @if ($document->total_canceled)
    <tr>
        <td class="align-top">Estado:</td>
        <td colspan="3">CANCELADO</td>
    </tr>
    @else
    <tr>
        <td class="align-top">Estado:</td>
        <td colspan="3">PENDIENTE DE PAGO</td>
    </tr>
    @endif --}}
    {{-- @if ($document->reference_data)
        <tr>
            <td class="align-top">D. Referencia:</td>
            <td colspan="3">{{ $document->reference_data }}</td>
        </tr>
    @endif --}}
    {{-- @if ($document->purchase_order)
        <tr>
            <td class="align-top">Orden de compra:</td>
            <td colspan="3">{{ $document->purchase_order }}</td>
        </tr>
    @endif --}}
</table>

@if ($document->isPointSystem())
    <table class="full-width mt-3">
        <tr>
            <td width="15%">P. ACUMULADOS</td>
            <td width="8px">:</td>
            <td>{{ $document->person->accumulated_points }}</td>

            <td width="140px">PUNTOS POR LA COMPRA</td>
            <td width="8px">:</td>
            <td>{{ $document->getPointsBySale() }}</td>
        </tr>
    </table>
@endif


@if ($document->guides)
<br/>
{{--<strong>Guías:</strong>--}}
<table>
    @foreach($document->guides as $guide)
        <tr>
            @if(isset($guide->document_type_description))
            <td>{{ $guide->document_type_description }}</td>
            @else
            <td>{{ $guide->document_type_id }}</td>
            @endif
            <td>:</td>
            <td>{{ $guide->number }}</td>
        </tr>
    @endforeach
</table>
@endif

<table class="full-width mt-10 mb-10">
    <thead class="">
    <tr class="bg-grey">
        <th class="border-top-bottom text-center py-2" class="cell-solid" width="8%">COD.</th>
        <th class="border-top-bottom text-center py-2" class="cell-solid" width="8%">CANT.</th>
        <th class="border-top-bottom text-center py-2" class="cell-solid" width="8%">UNIDAD</th>
        <th class="border-top-bottom text-left py-2" class="cell-solid">DESCRIPCIÓN</th>
        <th class="border-top-bottom text-right py-2" class="cell-solid" width="12%">P.UNIT</th>
        <th class="border-top-bottom text-right py-2" class="cell-solid" width="8%">DTO.</th>
        <th class="border-top-bottom text-right py-2" class="cell-solid" width="12%">TOTAL</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->items as $row)
        <tr>
            <td class="text-center align-top cell-solid-rl">{{ $row->item->internal_id }}</td>
            <td class="text-center align-top cell-solid-rl">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-center align-top cell-solid-rl">{{ $row->item->unit_type_id }}</td>
            <td class="text-left">
                @if($row->name_product_pdf)
                    {!!$row->name_product_pdf!!}
                @else
                    {!!$row->item->description!!}
                @endif
                @if (!empty($row->item->presentation)) {!!$row->item->presentation->description!!} @endif

                @if($row->attributes)
                    @foreach($row->attributes as $attr)
                        <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                    @endforeach
                @endif
                @if($row->discounts)
                    @foreach($row->discounts as $dtos)
                        <br/><span style="font-size: 9px">{{ $dtos->factor * 100 }}% {{$dtos->description }}</span>
                    @endforeach
                @endif

                @if($row->item->is_set == 1)

                 <br>
                 @inject('itemSet', 'App\Services\ItemSetService')
                 @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                     {{$item}}<br>
                 @endforeach
                @endif

                @if($row->item->used_points_for_exchange ?? false)
                    <br>
                    <span style="font-size: 9px">*** Canjeado por {{$row->item->used_points_for_exchange}}  puntos ***</span>
                @endif

            </td>
            <td class="text-right align-top cell-solid-rl">{{ number_format($row->unit_price, 2) }}</td>
            <td class="text-right align-top cell-solid-rl">
                @if($row->discounts)
                    @php
                        $total_discount_line = 0;
                        foreach ($row->discounts as $disto) {
                            $total_discount_line = $total_discount_line + $disto->amount;
                        }
                    @endphp
                    {{ number_format($total_discount_line, 2) }}
                @else
                0
                @endif
            </td>
            <td class="text-right align-top cell-solid-rl">{{ number_format($row->total, 2) }}</td>
        </tr>
        {{-- <tr>
            <td colspan="7" class="border-bottom"></td>
        </tr> --}}
    @endforeach

        @for($i = 0; $i < $cycle_items; $i++)
        <tr>
            <td class="p-1 text-center align-top desc cell-solid-rl"></td>
            <td class="p-1 text-center align-top desc cell-solid-rl">
            </td>
            <td class="p-1 text-center align-top desc cell-solid-rl"></td>
            <td class="p-1 text-left align-top desc text-upp cell-solid-rl">
            </td>
            <td class="p-1 text-right align-top desc cell-solid-rl"></td>
            <td class="p-1 text-right align-top desc cell-solid-rl">
            </td>
            <td class="p-1 text-right align-top desc cell-solid-rl"></td>
        </tr>
        @endfor
        <tr>
            @if(isset($configurationInPdf) && $configurationInPdf->show_seller_in_pdf)
                <td class="p-1 text-left align-top desc cell-solid" colspan="3"><strong> VENDEDOR:</strong> {{ $document->user->name }}</td>
            @endif
            <td class="p-1 text-left align-top desc cell-solid font-bold">
                {{-- SON: 
                @foreach(array_reverse( (array)$document->legends) as $row)
                    @if ($row->code == "1000")
                        {{ $row->value }} {{ $document->currency_type->description }}
                    @else
                        {{$row->code}}: {{ $row->value }}
                    @endif
                @endforeach --}}
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                OP. GRAVADA {{$document->currency_type->symbol}}
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold">{{ number_format($document->total_taxed, 2) }}</td>
        </tr>
        <tr>
            <td class="p-1 text-left align-top desc cell-solid" colspan="3" rowspan="6">
                @php
                    $total_packages = $document->items()->sum('quantity');
                @endphp
                <strong> Total bultos:</strong>
                    {{ number_format($total_packages, 0) }}
                <br>
                {{-- <strong> Total Peso:</strong>
                    {{$total_weight}} KG
                <br> --}}
            <td class="p-1 text-center align-top desc cell-solid " rowspan="6">
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                OP. INAFECTAS {{$document->currency_type->symbol}}
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
        </tr>
        <tr>
            <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                OP. EXONERADAS {{$document->currency_type->symbol}}
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
        </tr>
        <tr>
            <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                OP. GRATUITAS {{$document->currency_type->symbol}}
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold">{{ number_format($document->total_free, 2) }}</td>
        </tr>
        <tr>
            <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                TOTAL DCTOS. {{$document->currency_type->symbol}}
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold">{{ number_format($document->total_discount, 2) }}</td>
        </tr>
        <tr>

            <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                IGV. {{$document->currency_type->symbol}}
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold">{{ number_format($document->total_igv, 2) }}</td>
        </tr>
        <tr>
            <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                TOTAL A PAGAR. {{$document->currency_type->symbol}}
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold">{{ number_format($document->total, 2) }}</td>
        </tr>

        {{-- @if($document->total_exportation > 0)
            <tr>
                <td colspan="6" class="text-right font-bold ">OP. EXPORTACIÓN: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_exportation, 2) }}</td>
            </tr>
        @endif
        @if($document->total_free > 0)
            <tr>
                <td colspan="6" class="text-right font-bold">OP. GRATUITAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_free, 2) }}</td>
            </tr>
        @endif
        @if($document->total_unaffected > 0)
            <tr>
                <td colspan="6" class="text-right font-bold">OP. INAFECTAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
            </tr>
        @endif
        @if($document->total_exonerated > 0)
            <tr>
                <td colspan="6" class="text-right font-bold">OP. EXONERADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
            </tr>
        @endif --}}
        {{-- @if($document->total_taxed > 0)
             <tr>
                <td colspan="6" class="text-right font-bold">OP. GRAVADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        @endif --}}
        @if($document->total_discount > 0)
            <tr>
                <td colspan="6" class="text-right font-bold">{{(($document->total_prepayment > 0) ? 'ANTICIPO':'DESCUENTO TOTAL')}}: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
            </tr>
        @endif
        {{--<tr>
            <td colspan="6" class="text-right font-bold">IGV: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_igv, 2) }}</td>
        </tr>--}}

        @if($document->total_charge > 0 && $document->charges)
            <tr>
                <td colspan="6" class="text-right font-bold">CARGOS ({{$document->getTotalFactor()}}%): {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_charge, 2) }}</td>
            </tr>
        @endif


        @php
            $change_payment = $document->getChangePayment();
        @endphp

        @if($change_payment < 0)
            <tr>
                <td colspan="6" class="text-right font-bold">VUELTO: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format(abs($change_payment),2, ".", "") }}</td>
            </tr>
        @endif

    </tbody>
</table>

@if(isset($configurationInPdf) && $configurationInPdf->show_bank_accounts_in_pdf)
<table class="full-width">
    <tr>
        <td width="65%" style="text-align: top; vertical-align: top;">
            <br>
            @foreach($accounts as $account)
                <p>
                <span class="font-bold">{{$account->bank->description}}</span> {{$account->currency_type->description}}
                <span class="font-bold">N°:</span> {{$account->number}}
                @if($account->cci)
                - <span class="font-bold">CCI:</span> {{$account->cci}}
                @endif
                </p>
            @endforeach
        </td>
    </tr>
</table>
@endif
<br>

@if($document->payment_method_type_id && $payments->count() == 0)
    <table class="full-width">
        <tr>
            <td>
                <strong>PAGO: </strong>{{ $document->payment_method_type->description }}
            </td>
        </tr>
    </table>
@endif

@if($payments->count())

<table class="full-width">
<tr>
    <td>
    <strong>PAGOS:</strong> </td></tr>
        @php
            $payment = 0;
        @endphp
        @foreach($payments as $row)
            <tr><td>- {{ $row->date_of_payment->format('d/m/Y') }} - {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td></tr>
            @php
                $payment += (float) $row->payment;
            @endphp
        @endforeach
        <tr><td><strong>SALDO:</strong> {{ $document->currency_type->symbol }} {{ number_format($document->total - $payment, 2) }}</td>
    </tr>

</table>
@endif
@if ($document->terms_condition)
    <br>
    <table class="full-width">
        <tr>
            <td>
                <h6 style="font-size: 12px; font-weight: bold;">Términos y condiciones del servicio</h6>
                {!! $document->terms_condition !!}
            </td>
        </tr>
    </table>
@endif
</body>
</html>
