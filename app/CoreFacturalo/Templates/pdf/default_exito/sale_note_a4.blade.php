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
    {{--<title>{{ $tittle }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
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
        <td width="45%" class="pl-3">
            <div class="text-left">
                <h4 class="">{{ $company->name }}</h4>
                <h5>{{ 'RUC '.$company->number }}</h5>
                <h6 style="text-transform: uppercase;">
                    {{ ($establishment->address !== '-')? $establishment->address : '' }}
                    {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                    {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                    {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                </h6>

                <h6>{{ ($establishment->telephone !== '-')? 'Central telefónica: '.$establishment->telephone : '' }}</h6>

                <h6>{{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}</h6>

                @isset($establishment->web_address)
                    <h6>{{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}</h6>
                @endisset

                @isset($establishment->aditional_information)
                    <h6>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</h6>
                @endisset
            </div>
            
            </div>
        </td>
              <td width="20%" class="text-center">
                <table class="border-box full-width py-2 px-2">
                
                    <tr>
                     <td class="p-3 font-bold" style="font-size:13px">{{ 'RUC: '.$company->number }}</td>
                    </tr>
            
            
                    <tr style="background-color:lightgray">
                    <td class="pt-1 pb-1 pl-4 pr-4 font-bold" style="font-size:16px">NOTA DE VENTA</td>
                    </tr>

                  <tr>
                   <td class="p-3 font-bold" style="font-size:14px">{{ $tittle }}</td>
                     </tr>
                </table>
              </td>


</table>
<table class="full-width mt-4">
    <tr>
        <td width="120px">FECHA DE EMISIÓN</td>
        <td width="8px">:</td>
            <td>{{ $document->date_of_issue->format('Y-m-d') }}</td>
    </tr>
    <tr>
        <td width="140px">FECHA DE VENCIMIENTO</td>
        <td width="8px">:</td>
            <td>{{ $document->getFormatDueDate() }}</td>
     
    </tr>

    <tr>
        <td style="vertical-align: top;">CLIENTE</td>
        <td style="vertical-align: top;">:</td>
        <td width="45%">{{ $customer->name }}</td>
    </tr>


    <tr>
        <td>{{ $customer->identity_document_type->description }}</td>
        <td>:</td>
        <td>{{ $customer->number }}</td>
        

        @if ($document->due_date)
            <td class="align-top">Fecha Vencimiento:</td>
            <td>{{ $document->getFormatDueDate() }}</td>
        @endif

    </tr>
    @if ($customer->address !== '')
    <tr>
        <td class="align-top">DIRECCIÓN</td>
        <td>:</td>
        <td width="310px" style="text-transform: uppercase;">
            {{ strtoupper($customer->address) }}
            {{ ($customer->district_id !== '-')? ', '.strtoupper($customer->district->description) : '' }}
            {{ ($customer->province_id !== '-')? ', '.strtoupper($customer->province->description) : '' }}
            {{ ($customer->department_id !== '-')? '- '.strtoupper($customer->department->description) : '' }}
        </td>
    </tr>
    @endif
   
    @if ($document->plate_number !== null)
    <tr>
        <td width="15%">N° Placa:</td>
        <td width="85%">{{ $document->plate_number }}</td>
    </tr>
    
        <td class="align-top">Estado:</td>
        <td colspan="3">PENDIENTE DE PAGO</td>
    </tr>
    @endif
    @if ($document->observation)
    <tr>
        <td class="align-top">Observación:</td>
        <td colspan="3">{{ $document->observation }}</td>
    </tr>
    @endif
    @if ($document->reference_data)
        <tr>
            <td class="align-top">D. Referencia:</td>
            <td colspan="3">{{ $document->reference_data }}</td>
        </tr>
    @endif
    @if ($document->purchase_order)
        <tr>
            <td class="align-top">Orden de compra:</td>
            <td colspan="3">{{ $document->purchase_order }}</td>
        </tr>
    @endif
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
        <th class="border-box text-center py-2" width="8%" style="background-color:lightgray">CANT.</th>
        <th class="border-box text-center py-2" width="9%" style="background-color:lightgray">UNIDAD</th>
        <th class="border-box text-center py-2" width="9%" style="background-color:lightgray">CÓDIGO</th>
        <th class="border-top-bottom text-left py-2" width="51%" style="background-color:lightgray">DESCRIPCIÓN</th>
        <th class="border-box text-center py-2" width="10%" style="background-color:lightgray">P.UNIT</th>
        <th class="border-box text-center py-2" width="7%" style="background-color:lightgray">DTO.</th>
        <th class="border-box text-center py-2" width="10%" style="background-color:lightgray">TOTAL</th>
    </tr>
    </thead>
    <tbody>
        @php
           $height = 400;
          @endphp

    @foreach($document->items as $row)
        @inject('items', 'App\Models\Tenant\Item')
        @php
            $internal_id = isset($row->item->internal_id) ? $row->item->internal_id : $items->find($row->item_id)->internal_id;
        @endphp
        <tr>
            {{-- <td class="text-center align-top">{{ $internal_id }}</td> --}}
            <td class="text-center align-top border-left">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            
            <td class="text-center border-left align-top">{{ $row->item->unit_type_id }}</td>
            <td class="text-center border-left align-top">{{ $row->item->internal_id }}</td>
            <td class="text-left border-left align-top">{{ $row->item->description }}{!!$row->name_product_pdf!!}
                @if($row->attributes)
            @foreach($row->attributes as $attr)
                <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                @php
                        if ($attr->attribute_type_id !== '5032') {
                            $height = $height - 20;
                        }
                    @endphp
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
            <td class="text-right border-left align-top">{{ number_format($row->unit_price, 2) }}</td>
            <td class="text-right border-left align-top">
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
            <td class="p-0 text-right align-top border-left border-right">{{ number_format($row->total, 2) }}</td>
        </tr>
-------------------------------------------------------------------
@php
$height = $height - 20;
@endphp

@endforeach
  @if ($height > 0)
<tr>
 <td class="border-left" style="height: {{ $height }}px"></td>
 <td class="border-left"></td>
 <td class="border-left"></td>
 <td class="border-left"></td>
 <td class="border-left"></td>
 <td class="border-left"></td>
 <td class="border-left border-right"></td>
 </tr>
@endif


---------------------------------

        
        <tr>
            <td colspan="7" class="border-bottom"></td>
        </tr>
        @if($document->total_exportation > 0)


            <tr>
                <td colspan="6" class="text-right font-bold">OP. EXPORTACIÓN: {{ $document->currency_type->symbol }}</td>
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
        @endif
        -------------------------------------------------------------------------------------------------------------------------------------

      @if($document->total_taxed > 0)
             <tr>
                <td colspan="6" class="text-right font-bold">OP. GRAVADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        @endif

        -------------------------------------------------------------------------------------------------------------------------------------

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

        <tr>
            <td colspan="6" class="text-right font-bold">TOTAL A PAGAR: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
        </tr>

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


<table class="border-box mt-2">

    <tr class="m-3">
        <td></td>
    </tr>
       <td class="p-2">

          @if($payments->count())
              <tr>
                <td style="font-size:11px" >
                    <strong> MÉTODO DE PAGO:</strong> </td></tr>
                   @php
                   $payment = 0;
                   @endphp
                  @foreach($payments as $row)
                   <td>  {{ $row->payment_method_type->description }} </td>
                   @php
                    $payment += (float) $row->payment;
                  @endphp
                   @endforeach

                    </tr>


                   <tr class="m-4">
        <td> </td>
    </tr>
</table>

{{-- NUMERO DE CUENTA --}} 
<br>

@if (isset($configurationInPdf) && $configurationInPdf->show_bank_accounts_in_pdf)
<strong style="font-size:12px">CUENTAS BANCARIAS:</strong>
<table>
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
                    <img src="data:{{mime_content_type($logoPath)}};base64, {{base64_encode(file_get_contents($logoPath))}}" alt="{{$account->bank->description}}" style="width: 44px; height: 15px; padding: 1px 0;">
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
            <br>
            
            
            @endforeach
        @if (isset($configurationInPdf) && $configurationInPdf->show_seller_in_pdf)
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
        @endif 
    
</table>
@endif


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
