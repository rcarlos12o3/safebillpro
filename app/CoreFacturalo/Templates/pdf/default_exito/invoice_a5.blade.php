@php
    $establishment = $document->establishment;
    $customer = $document->customer;
    $invoice = $document->invoice;
    $document_base = ($document->note) ? $document->note : null;

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();

    if($document_base) {
        $affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

    } else {
        $affected_document_number = null;
    }

    $payments = $document->payments;
    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');
    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();

    $bnacion_logo = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'default_exito'.DIRECTORY_SEPARATOR.'banknacion_logo');

    $bcp_logo = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'default_exito'.DIRECTORY_SEPARATOR.'bcp_logo');

    $bbva_logo = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'default_exito'.DIRECTORY_SEPARATOR.'bbva_logo');
    
    $scotiabank_logo = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'default_exito'.DIRECTORY_SEPARATOR.'scotiabank_logo');

    $interbank_logo = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'default_exito'.DIRECTORY_SEPARATOR.'interbank_logo');

    $empty_logo = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'default_exito'.DIRECTORY_SEPARATOR.'empty_logo');

    $configurationInPdf= App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationInPdf();
@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
@if($document->state_type->id == '11')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:50%;">
        <img
            src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}"
            alt="anulado" class="" style="opacity: 0.6;">
    </div>
@endif
<table class="full-width">
    <tr>
        @if($company->logo)
            <td width="17%">
                <div class="company_logo_box">
                    <img
                        src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}"
                        alt="{{$company->name}}" class="company_logo" style="max-width: 110px;">
                </div>
            </td>
        @else
            <td width="20%">
                {{--<img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px">--}}
            </td>
        @endif
        <td width="45%" class="pr-5">
            <div class="text-left">
                <h4 class="">{{ $company->name }}</h4>
                {{-- <h5>{{ 'RUC '.$company->number }}</h5> --}}
                <h6 style="text-transform: uppercase;">
                    {{ ($establishment->address !== '-')? $establishment->address : '' }}
                    {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                    {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                    {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                </h6>

                @isset($establishment->trade_address)
                    <h6>{{ ($establishment->trade_address !== '-')? 'D. Comercial: '.$establishment->trade_address : '' }}</h6>
                @endisset

                <h6>{{ ($establishment->telephone !== '-')? 'Central telefónica: '.$establishment->telephone : '' }}</h6>

                <h6>{{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}</h6>

                @isset($establishment->web_address)
                    <h6>{{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}</h6>
                @endisset

                @isset($establishment->aditional_information)
                    <h6>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</h6>
                @endisset
            </div>
        </td>
        <td width="30%" class="text-center">
            <table class="border-box full-width">
            <tr>
                <td class="p-2 font-bold" style="font-size:13px">{{ 'RUC: '.$company->number }}</td>
            </tr>
            <tr style="background-color:lightgray">
                <td class="pt-3 pb-3 font-bold" style="font-size:14px">{{ $document->document_type->description }}</td>
            </tr>
            <tr>
                <td class="p-2 font-bold" style="font-size:13px">{{ $document_number }}</td>
            </tr>
            </table>
        </td>
    </tr>
</table>
<table class="full-width mt-2" style="font-size: 12">
    <tr>
        <td width="120px" style="font-size: 12">FECHA EMISIÓN</td>
        <td width="8px">:</td>
        <td>{{$document->date_of_issue->format('Y-m-d')}}</td>

        @if ($document->detraction)
            <td width="120px">N. CTA DETRACCIONES</td>
            <td width="8px" class="align-top">:</td>
            <td class="align-top">{{ $document->detraction->bank_account}}</td>
        @endif
    </tr>

    @if($invoice)
        <tr>
            <td width="140px">FECHA VENCIMIENTO</td>
            <td width="8px" class="align-top"> :</td>
            <td class="align-top">{{$invoice->date_of_due->format('Y-m-d')}}</td>
        </tr>
    @endif

    @if ($document->detraction)
        <td width="120px">B/S SUJETO A DETRACCIÓN</td>
        <td width="8px">:</td>
        @inject('detractionType', 'App\Services\DetractionTypeService')
        <td width="220px">{{$document->detraction->detraction_type_id}}
            - {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id ) }}</td>

    @endif
    <tr>
        <td style="vertical-align: top;">CLIENTE</td>
        <td style="vertical-align: top;">:</td>
        <td style="vertical-align: top;">
            {{ $customer->name }}
            @if ($customer->internal_code ?? false)
                <br>
                <small>{{ $customer->internal_code ?? '' }}</small>
            @endif
        </td>
        @if ($document->detraction)
            <td width="120px">MÉTODO DE PAGO</td>
            <td width="8px">:</td>
            <td width="220px">{{ $detractionType->getPaymentMethodTypeDescription($document->detraction->payment_method_id ) }}</td>
        @endif
    </tr>
    <tr>
        <td>{{ $customer->identity_document_type->description }}</td>
        <td>:</td>
        <td>{{$customer->number}}</td>


        @if ($document->detraction)
            <td width="120px">P. DETRACCIÓN</td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->percentage}}%</td>
        @endif
    </tr>
    @if ($customer->address !== '')
        <tr>
            <td class="align-top">DIRECCIÓN:</td>
            <td>:</td>
            <td>
                {{ $customer->address }}
                {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
                {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
            </td>

            @if ($document->detraction)
                <td width="120px">MONTO DETRACCIÓN</td>
                <td width="8px">:</td>
                <td>S/ {{ $document->detraction->amount}}</td>
    @endif
    @if ($document->detraction)
        @if($document->detraction->pay_constancy)
            <tr>
                <td colspan="3">
                </td>
                <td width="120px">C. PAGO</td>
                <td width="8px">:</td>
                <td>{{ $document->detraction->pay_constancy}}</td>
            </tr>
            @endif
            @endif
            </tr>
        @endif


        @if ($document->reference_data)
            <tr>
                <td width="120px">D. REFERENCIA</td>
                <td width="8px">:</td>
                <td>{{ $document->reference_data}}</td>
            </tr>
        @endif
</table>

@if ($document->guides)
    <br/>
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


@if ($document->dispatch)
    <br/>
    <strong>Guías de remisión</strong>
    <table>
        <tr>
            <td>{{ $document->dispatch->number_full }}</td>
        </tr>
    </table>

@elseif ($document->reference_guides)
    @if (count($document->reference_guides) > 0)
        <br/>
        <strong>Guias de remisión</strong>
        <table>
            @foreach($document->reference_guides as $guide)
                <tr>
                    <td>{{ $guide->series }}</td>
                    <td>-</td>
                    <td>{{ $guide->number }}</td>
                </tr>
            @endforeach
        </table>
    @endif
@endif


<table class="full-width mt-3">
    @if ($document->prepayments)
        @foreach($document->prepayments as $p)
            <tr>
                <td width="120px">ANTICIPO</td>
                <td width="8px">:</td>
                <td>{{$p->number}}</td>
            </tr>
        @endforeach
    @endif
    @if ($document->purchase_order)
        <tr>
            <td width="120px">ORDEN DE COMPRA</td>
            <td width="8px">:</td>
            <td>{{ $document->purchase_order }}</td>
        </tr>
    @endif
    @if ($document->quotation_id)
        <tr>
            <td width="120px">COTIZACIÓN</td>
            <td width="8px">:</td>
            <td>{{ $document->quotation->identifier }}</td>
            @isset($document->quotation->delivery_date)
                <td width="120px">F. ENTREGA</td>
                <td width="8px">:</td>
                <td>{{ $document->date_of_issue->addDays($document->quotation->delivery_date)->format('d-m-Y') }}</td>
            @endisset
        </tr>
    @endif
    @isset($document->quotation->sale_opportunity)
        <tr>
            <td width="120px">O. VENTA</td>
            <td width="8px">:</td>
            <td>{{ $document->quotation->sale_opportunity->number_full}}</td>
        </tr>
    @endisset
    @if(!is_null($document_base))
        <tr>
            <td width="120px">DOC. AFECTADO</td>
            <td width="8px">:</td>
            <td>{{ $affected_document_number }}</td>

            <td width="120px">TIPO DE NOTA</td>
            <td width="8px">:</td>
            <td>{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>
        </tr>
        <tr>
            <td>DESCRIPCIÓN</td>
            <td>:</td>
            <td>{{ $document_base->note_description }}</td>
        </tr>
    @endif
</table>

{{--<table class="full-width mt-3">--}}
{{--<tr>--}}
{{--<td width="25%">Documento Afectado:</td>--}}
{{--<td width="20%">{{ $document_base->affected_document->series }}-{{ $document_base->affected_document->number }}</td>--}}
{{--<td width="15%">Tipo de nota:</td>--}}
{{--<td width="40%">{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>--}}
{{--</tr>--}}
{{--<tr>--}}
{{--<td class="align-top">Descripción:</td>--}}
{{--<td class="text-left" colspan="3">{{ $document_base->note_description }}</td>--}}
{{--</tr>--}}
{{--</table>--}}

<table class="full-width mt-1 mb-0">
    <thead class="">
    <tr class="">
        <th class="border-box text-center py-1" width="8%" style="background-color:lightgray; font-size:11px">CANT.</th>
        <th class="border-top-bottom text-center py-2" width="8%" style="background-color:lightgray; font-size:11px">UNIDAD</th>
        <th class="border-top-bottom text-left py-2" style="background-color:lightgray; font-size:11px">DESCRIPCIÓN</th>
        <th class="border-box text-center py-2" width="10%" style="background-color:lightgray; font-size:11px">MODELO</th>
        <th class="border-box text-center py-2" width="8%" style="background-color:lightgray; font-size:11px">LOTE</th>
        <th class="border-box text-center py-2" width="8%" style="background-color:lightgray; font-size:11px">SERIE</th>
        <th class="border-box text-center py-2" width="12%" style="background-color:lightgray; font-size:11px">P.UNIT</th>
        <th class="border-box text-center py-2" width="7%" style="background-color:lightgray; font-size:11px">DTO.</th>
        <th class="border-box text-center py-2" width="12%" style="background-color:lightgray; font-size:11px">TOTAL</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->items as $row)
        <tr class= "">
            <td class="text-center align-top" style="border-left:1px solid black; border-right:1px solid black">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-center align-top"  style="border-left:1px solid black">{{ $row->item->unit_type_id }}</td>
            <td class="text-left align-top">
                @if($row->name_product_pdf)
                    {!!$row->name_product_pdf!!}
                @else
                    {!!$row->item->description!!}
                @endif

                @if($row->total_isc > 0)
                    <br/><span style="font-size: 9px">ISC : {{ $row->total_isc }} ({{ $row->percentage_isc }}%)</span>
                @endif

                @if (!empty($row->item->presentation)) {!!$row->item->presentation->description!!} @endif

                @if($row->total_plastic_bag_taxes > 0)
                    <br/><span style="font-size: 9px">ICBPER : {{ $row->total_plastic_bag_taxes }}</span>
                @endif

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

                @if($row->charges)
                    @foreach($row->charges as $charge)
                        <br/><span style="font-size: 9px">{{ $document->currency_type->symbol}} {{ $charge->amount}} ({{ $charge->factor * 100 }}%) {{$charge->description }}</span>
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
                    <span
                        style="font-size: 9px">*** Canjeado por {{$row->item->used_points_for_exchange}}  puntos ***</span>
                @endif

                @if($document->has_prepayment)
                    <br>
                    *** Pago Anticipado ***
                @endif
            </td>
            <td class="text-left align-top" style="border-left:1px solid black">{{ $row->item->model ?? '' }}</td>
            <td class="text-center align-top" style="border-left:1px solid black">
                @inject('itemLotGroup', 'App\Services\ItemLotsGroupService')
                {{ $itemLotGroup->getLote($row->item->IdLoteSelected) }}

            </td>
            <td class="text-center align-top" style="border-left:1px solid black">
                @isset($row->item->lots)
                    @foreach($row->item->lots as $lot)
                        @if( isset($lot->has_sale) && $lot->has_sale)
                            <span style="font-size: 9px">{{ $lot->series }}</span><br>
                        @endif
                    @endforeach
                @endisset
            </td>

            @if ($configuration_decimal_quantity->change_decimal_quantity_unit_price_pdf)
                <td class="text-right align-top" style="border-left:1px solid black">{{ $row->generalApplyNumberFormat($row->unit_price, $configuration_decimal_quantity->decimal_quantity_unit_price_pdf) }}</td>
            @else
                <td class="text-right align-top" style="border-left:1px solid black">{{ number_format($row->unit_price, 2) }}</td>
            @endif

            <td class="text-center align-top" style="border-left:1px solid black">
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
            <td class="p-0 text-right align-top" style="border-left:1px solid black; border-right:1px solid black;">{{ number_format($row->total, 2) }}</td>
        </tr>
    @endforeach
    <tr>
            {{--<td colspan="9" class="border-bottom"></td>--}}
            <td colspan="9" class="p-0 border-bottom"></td>
        </tr>

    @if ($document->prepayments)
        @foreach($document->prepayments as $p)
            <tr>
                <td class="text-center align-top">1</td>
                <td class="text-center align-top">NIU</td>
                <td class="text-left align-top">
                    ANTICIPO: {{($p->document_type_id == '02')? 'FACTURA':'BOLETA'}} NRO. {{$p->number}}
                </td>
                <td class="text-center align-top"></td>
                <td class="text-center align-top"></td>
                <td class="text-center align-top"></td>
                <td class="text-right align-top">-{{ number_format($p->total, 2) }}</td>
                <td class="text-right align-top">0</td>
                <td class="text-right align-top">-{{ number_format($p->total, 2) }}</td>
            </tr>
            <tr>
                {{--<td colspan="9" class="border-bottom"></td>--}} 
            </tr>
        @endforeach
    @endif

    @if($document->total_exportation > 0)
        <tr>
            <td colspan="8" class="text-right font-bold">OP. EXPORTACIÓN: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_exportation, 2) }}</td>
        </tr>
    @endif
    @if($document->total_free > 0)
        <tr>
            <td colspan="8" class="text-right font-bold">OP. GRATUITAS: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_free, 2) }}</td>
        </tr>
    @endif
    @if($document->total_unaffected > 0)
        <tr>
            <td colspan="8" class="text-right font-bold">OP. INAFECTAS: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
        </tr>
    @endif
    @if($document->total_exonerated > 0)
        <tr>
            <td colspan="8" class="text-right font-bold">OP. EXONERADAS: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
        </tr>
    @endif

    @if ($document->document_type_id === '07')
        @if($document->total_taxed >= 0)
            <tr>
                <td colspan="8" class="text-right">OP. GRAVADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        @endif
    @elseif($document->total_taxed > 0)
        <tr>
            <td colspan="8" class="text-right">OP. GRAVADAS: {{ $document->currency_type->symbol }}</td>
            <td class="text-right">{{ number_format($document->total_taxed, 2) }}</td>
        </tr>
    @endif

    @if($document->total_plastic_bag_taxes > 0)
        <tr>
            <td colspan="8" class="text-right font-bold">ICBPER: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
        </tr>
    @endif
    <tr>
        <td colspan="8" class="text-right">IGV: {{ $document->currency_type->symbol }}</td>
        <td class="text-right">{{ number_format($document->total_igv, 2) }}</td>
    </tr>

    @if($document->total_isc > 0)
        <tr>
            <td colspan="8" class="text-right font-bold">ISC: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_isc, 2) }}</td>
        </tr>
    @endif

    @if($document->total_discount > 0 && $document->subtotal > 0)
        <tr>
            <td colspan="8" class="text-right font-bold">SUBTOTAL: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->subtotal, 2) }}</td>
        </tr>
    @endif

    @if($document->total_discount > 0)
        <tr>
            <td colspan="8"
                class="text-right font-bold">{{(($document->total_prepayment > 0) ? 'ANTICIPO':'DESCUENTO TOTAL')}}
                : {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
        </tr>
    @endif

    @if($document->total_charge > 0)
        @if($document->charges)
            @php
                $total_factor = 0;
                foreach($document->charges as $charge) {
                    $total_factor = ($total_factor + $charge->factor) * 100;
                }
            @endphp
            <tr>
                <td colspan="8" class="text-right font-bold">CARGOS ({{$total_factor}}
                    %): {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_charge, 2) }}</td>
            </tr>
        @else
            <tr>
                <td colspan="8" class="text-right font-bold">CARGOS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_charge, 2) }}</td>
            </tr>
        @endif
    @endif

    @if($document->perception)
        <tr>
            <td colspan="8" class="text-right font-bold">IMPORTE TOTAL: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
        </tr>
        <tr>
            <td colspan="8" class="text-right font-bold">PERCEPCIÓN: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->perception->amount, 2) }}</td>
        </tr>
        <tr>
            <td colspan="8" class="text-right font-bold">TOTAL A PAGAR: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format(($document->total + $document->perception->amount), 2) }}</td>
        </tr>
    @elseif($document->retention)
        <tr>
            <td colspan="8" class="text-right font-bold"
                style="font-size: 12px;">IMPORTE TOTAL: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold" style="font-size: 11px;">{{ number_format($document->total, 2) }}</td>
        </tr>
        <tr>
            <td colspan="8" class="text-right">TOTAL RETENCIÓN ({{ $document->retention->percentage * 100 }}
                %): {{ $document->currency_type->symbol }}</td>
            <td class="text-right">{{ number_format($document->retention->amount, 2) }}</td>
        </tr>
        <tr>
            <td colspan="8" class="text-right">IMPORTE NETO: {{ $document->currency_type->symbol }}</td>
            <td class="text-right">{{ number_format(($document->total - $document->retention->amount), 2) }}</td>
        </tr>
    @else
        <tr>
            <td colspan="8" class="text-right font-bold">TOTAL A PAGAR: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
        </tr>
    @endif

    @if(($document->retention || $document->detraction) && $document->total_pending_payment > 0)
        <tr>
            <td colspan="8" class="text-right font-bold">M. PENDIENTE: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_pending_payment, 2) }}</td>
        </tr>
    @endif

    @if($balance < 0)
        <tr>
            <td colspan="8" class="text-right font-bold">VUELTO: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format(abs($balance),2, ".", "") }}</td>
        </tr>
    @endif

    </tbody>
</table>

<table class="full-width">
    <tr>
        <td width="65%" style="text-align: top; vertical-align: top; font-size:11px">
            @foreach(array_reverse( (array) $document->legends) as $row)
                @if ($row->code == "1000")
                    <p style="text-transform: uppercase;">Son: <span
                            class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></p>
                    @if (count((array) $document->legends)>1)
                        <p><span class="font-bold">Leyendas</span></p>
                    @endif
                @else
                    <p> {{$row->code}}: {{ $row->value }} </p>
                @endif

            @endforeach
            <br/>

            @if ($document->detraction)
                <p>
                <span class="font-bold">
                Operación sujeta al Sistema de Pago de Obligaciones Tributarias
                </span>
                </p>
                <br/>

            @endif
            @if ($customer->department_id == 16)
                <br/><br/><br/>
                <div>
                    <center>
                        Representación impresa del Comprobante de Pago Electrónico.
                        <br/>Esta puede ser consultada en:
                        <br/><b>{!! url('/buscar') !!}</b>
                        <br/> "Bienes transferidos en la Amazonía
                        <br/>para ser consumidos en la misma".
                    </center>
                </div>
                <br/>
            @endif
            @foreach($document->additional_information as $information)
                @if ($information)
                    @if ($loop->first)
                        <strong>Información adicional</strong>
                    @endif
                    <p>@if(\App\CoreFacturalo\Helpers\Template\TemplateHelper::canShowNewLineOnObservation())
                            {!! \App\CoreFacturalo\Helpers\Template\TemplateHelper::SetHtmlTag($information) !!}
                        @else
                            {{$information}}
                        @endif</p>
                @endif
            @endforeach
            <br>
            <table>
             @if (isset($configurationInPdf) && $configurationInPdf->show_bank_accounts_in_pdf)
                @if(in_array($document->document_type->id,['01','03']))
                    @foreach($accounts as $account)
                        <tr>
                            {{-- Mostrar la imagen correspondiente al banco --}}
                            <td style="width: 25px;">
                                @php
                                    $logoPath = '';
                                    switch ($account->bank->description) {
                                        case "BANCO DE CREDITO DEL PERU":
                                            $logoPath = $bcp_logo . '.png';
                                            break;
                                        case "CUENTA DE DETRACCIONES":
                                            $logoPath = $bnacion_logo . '.png';
                                        break;
                                        case "BBVA CONTINENTAL":
                                            $logoPath = $bbva_logo . '.png';
                                            break;
                                        case 'BANCO SCOTIABANK':
                                            $logoPath = $scotiabank_logo . '.png';
                                            break;
                                        case 'INTERBANK':
                                            $logoPath = $interbank_logo . '.png';
                                            break;
                                        default:
                                            $logoPath = $empty_logo . '.png';
                                            break;
                                    }
                                @endphp
                                <img src="data:{{mime_content_type($logoPath)}};base64, {{base64_encode(file_get_contents($logoPath))}}" alt="{{$account->bank->description}}" style="width: 25px; height: 10px; padding: 1px 0;">
                            </td>
            
                            {{-- Descripción del banco --}}
                            <td style="font-size:11px">
                                <strong>{{$account->bank->description}}</strong>
                                {{$account->currency_type->description}} Nº: {{$account->number}}
                                @if($account->cci)
                                    <br>CCI: {{$account->cci}}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif
                @endif
            </table>
                    


            @if ($document->retention)
                <p><strong>Información de la retención</strong></p>
                <p>
                    Base imponible: {{ $document->currency_type->symbol}} {{ $document->retention->base }} /
                    Porcentaje: {{ $document->retention->percentage * 100 }}% /
                    Monto: {{ $document->currency_type->symbol}} {{ $document->retention->amount }}
                </p>
            @endif

        </td>
        <td width="30%" height="50px" class="text-right">
            <img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px; max-width: 90px" width="16%"/>
            <p style="font-size: 9px">Código Hash: {{ $document->hash }}</p>
        </td>
    </tr>
</table>


@php
    $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);

@endphp
{{-- Condicion de pago  Crédito / Contado --}}
<table class="full-width">
    <tr>
        <td>
            <strong>CONDICIÓN DE PAGO: {{ $paymentCondition }} </strong>
        </td>
    </tr>
</table>

@if($document->payment_method_type_id)
    <table class="full-width">
        <tr>
            <td>
                <strong>MÉTODO DE PAGO: </strong>{{ $document->payment_method_type->description }}
            </td>
        </tr>
    </table>
@endif
@if ($document->payment_condition_id === '01')
    @if($payments->count())
        <table class="full-width">
            <tr>
                <td>
                    <strong>PAGOS:</strong></td>
            </tr>
            @php
                $payment = 0;
            @endphp
            @foreach($payments as $row)
                <tr>
                    <td>&#8226; {{ $row->payment_method_type->description }}
                        - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>
                </tr>
                @endforeach
                </tr>
        </table>
    @endif
@else
    <table class="full-width">
        @foreach($document->fee as $key => $quote)
            <tr>
                <td>
                    &#8226; {{ (empty($quote->getStringPaymentMethodType()) ? 'Cuota #'.( $key + 1) : $quote->getStringPaymentMethodType()) }}
                    / Fecha: {{ $quote->date->format('d-m-Y') }} /
                    Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}</td>
            </tr>
            @endforeach
            </tr>
    </table>
@endif
@if (isset($configurationInPdf) && $configurationInPdf->show_seller_in_pdf)
<br>
<table class="full-width">
    <tr>
        <td>
            <strong>Vendedor:</strong>
        </td>
    </tr>
    <tr>
        @if ($document->seller)
            <td>{{ $document->seller->name }}</td>
        @else
            <td>{{ $document->user->name }}</td>
        @endif
    </tr>
</table>
@endif
@if($document->retention)
    <br>
    <table class="full-width">
        <tr>
            <td>
                <strong>Información de la retención:</strong>
            </td>
            <td>Base imponible de la retención:
                S/ {{ round($document->retention->amount_pen / $document->retention->percentage, 2) }}</td>
            <td>Porcentaje de la retención {{ $document->retention->percentage * 100 }}%</td>
            <td>Monto de la retención S/ {{ $document->retention->amount_pen }}</td>
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
{{-- <div class="" style="text-alaign:center; position: absolute; bottom:20px">
    <table height="300px" class="full-width">
        <tr>
            <td class="text-center" style=" font-size:11px">
               Comprobante emitido a través de <span class="font-bold" style="font-size:13px; text-decoration:underline; color:blue">www.tuexito.pe</span>
            </td>
        </tr>
        <tr>
            <td class="text-center desc" style="font-size:08px">Para consultar el comprobante ingresar a {!! url('/buscar') !!}</td>
        </tr>
    </table>
</div> --}}
</body>
</html>
