@php
    $establishment = $document->establishment;
    $customer = $document->customer;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');

    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    // $document_type_driver = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->driver->identity_document_type_id);

@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
<table class="full-width">
    <tr>
        @if($company->logo)
            <td width="10%">
                <img
                    src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}"
                    alt="{{$company->name}}" alt="{{ $company->name }}" class="company_logo" style="max-width: 300px">
            </td>
        @else
            <td width="10%">
                {{--<img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px">--}}
            </td>
        @endif
        <td width="50%" class="pl-3">
            <div class="text-left">
                <h3 class="">{{ $company->name }}</h3>
                <h4>{{ 'RUC '.$company->number }}</h4>
                <h5 style="text-transform: uppercase;">
                    {{ ($establishment->address !== '-')? $establishment->address : '' }}
                    {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                    {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                    {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                </h5>
                <h5>{{ ($establishment->email !== '-')? $establishment->email : '' }}</h5>
                <h5>{{ ($establishment->telephone !== '-')? $establishment->telephone : '' }}</h5>
            </div>
        </td>
        <td width="40%" class="border-box p-4 text-center">
            <h4 class="text-center">{{ $document->document_type->description }}</h4>
            <h3 class="text-center">{{ $document_number }}</h3>
        </td>
    </tr>
</table>
@if($document->transfer_reason_type_id === '04')
    <table class="full-width border-box mt-10 mb-10">
        <thead>
        <tr>
            <th class="border-bottom text-left">DESTINATARIO</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Razón Social: {{ $company->name }}</td>
        </tr>
        <tr>
            <td>RUC: {{ $company->number }}
            </td>
        </tr>
        </tbody>
    </table>
@else
    <table class="full-width border-box mt-10 mb-10">
        <thead>
        <tr>
            <th class="border-bottom text-left">DESTINATARIO</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Razón Social: {{ $customer->name }}</td>
        </tr>
        <tr>
            <td>{{ $customer->identity_document_type->description }}: {{ $customer->number }}
            </td>
        </tr>
        <tr>
            @if($document->transfer_reason_type_id === '09')
                <td>Dirección: {{ $customer->address }} - {{ $customer->country->description }}
                </td>
            @else
                <td>Dirección: {{ $customer->address }}
                    {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
                    {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                    {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
                </td>
            @endif
        </tr>
        @if ($customer->telephone)
            <tr>
                <td>Teléfono:{{ $customer->telephone }}</td>
            </tr>
        @endif
        <tr>
            <td>Vendedor: {{ $document->user->name }}</td>
        </tr>
        </tbody>
    </table>
@endif
<table class="full-width border-box mt-10 mb-10">
    <thead>
    <tr>
        <th class="border-bottom text-left" colspan="2">ENVIO</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>Fecha Emisión: {{ $document->date_of_issue->format('Y-m-d') }}</td>
        <td>Fecha Inicio de Traslado: {{ $document->date_of_shipping->format('Y-m-d') }}</td>
    </tr>
    <tr>
        <td>Motivo Traslado: {{ $document->transfer_reason_type->description }}</td>
        <td>Modalidad de Transporte: {{ $document->transport_mode_type->description }}</td>
    </tr>

    @if($document->transfer_reason_description)
        <tr>
            <td colspan="2">Descripción de motivo de traslado: {{ $document->transfer_reason_description }}</td>
        </tr>
    @endif

    @if($document->related)
        <tr>
            <td>Número de documento (DAM): {{ $document->related->number }}</td>
            <td>Tipo documento relacionado: {{ $document->getRelatedDocumentTypeDescription() }}</td>
        </tr>
    @endif

    <tr>
        <td>Peso Bruto Total({{ $document->unit_type_id }}): {{ $document->total_weight }}</td>
        @if($document->packages_number)
            <td>Número de Bultos: {{ $document->packages_number }}</td>
        @endif
    </tr>
    <tr>
        <td colspan="2">P.Partida: {{ $document->origin->location_id }} - {{ $document->origin->address }}
            {{ ($establishment->address !== '-')? $establishment->address : '' }}
            {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
            {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
            {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
        </td>
    </tr>
    <tr>
        <td colspan="2">P.Llegada: {{ $document->delivery->location_id }} - {{ $document->delivery->address }}
            @php
                use Illuminate\Support\Facades\DB;
                $delivery = DB::connection('tenant')->table('districts')
                    ->join('provinces', 'districts.province_id', '=', 'provinces.id')
                    ->join('departments', 'provinces.department_id', '=', 'departments.id')
                    ->where('districts.id', '=', $document->delivery->location_id)
                    ->select('districts.description as district_description', 'provinces.description as province_description','departments.description as department_description')
                    ->first();
            @endphp
            {{ ($delivery->district_description !== '-')? ', '.$delivery->district_description : '' }}
            {{ ($delivery->province_description !== '-')? ', '.$delivery->province_description : '' }}
            {{ ($delivery->department_description !== '-')? '- '.$delivery->department_description : '' }}
        </td>
    </tr>
    <tr>
        @if($document->order_form_external)
            <td>Orden de pedido: {{ $document->order_form_external }}</td>
        @endif
        @if($document->date_delivery_to_transport)
            <td>Fecha de entrega de bienes al Transportista: {{$document->date_delivery_to_transport->format('Y-m-d')}}</td>
        @endif
    </tr>
    </tbody>
</table>
<table class="full-width border-box mt-10 mb-10">
    <thead>
    <tr>
        <th class="border-bottom text-left" colspan="2">TRANSPORTE</th>
    </tr>
    </thead>
    <tbody>
    @if($document->transport_mode_type_id === '01')
        @php
            $document_type_dispatcher = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->dispatcher->identity_document_type_id);
        @endphp
        <tr>
            <td>Nombre y/o razón social: {{ $document->dispatcher->name }}</td>
            <td>{{ $document_type_dispatcher->description }}: {{ $document->dispatcher->number }}</td>
        </tr>
        @if($document->has_transport_driver_01)
        <tr>
            @if($document->transport_data)
            <td>Número de placa del vehículo Principal: {{ $document->transport_data['plate_number'] }}</td>
            @endif
            @if($document->transport_data['tuc'])
                <td>Certificado de habilitación vehicular: {{ $document->transport_data['tuc'] }}</td>
            @endif
        </tr>
        <tr>
            @if($document->driver->number)
                <td>Conductor Principal: {{$document->driver->name}}</td>
            @endif
        </tr>
        <tr>
            @if($document->driver->license)
                <td>Licencia del conductor: {{ $document->driver->license }}</td>
            @endif
        </tr>
        @endif
    @else
    <tr>
        @if($document->transport_data)
        <td>Número de placa del vehículo Principal: {{ $document->transport_data['plate_number'] }}</td>
        @endif
        @if($document->transport_data['tuc'])
            <td>Certificado de habilitación vehicular: {{ $document->transport_data['tuc'] }}</td>
        @endif
    </tr>
    <tr>
    </tr>
    <tr>
        @if($document->driver->number)
            <td>Conductor Principal: {{$document->driver->name}}</td>
        @endif
    </tr>
    <tr>
        @if($document->secondary_license_plates)
            @if($document->secondary_license_plates->semitrailer)
                <td>Número de placa semirremolque: {{ $document->secondary_license_plates->semitrailer }}</td>
            @endif
        @endif
        @if($document->driver->license)
            <td>Licencia del conductor: {{ $document->driver->license }}</td>
        @endif
    </tr>
    @endif
    </tbody>
</table>
@if($document->secondary_transports)
    <table class="full-width border-box mt-10 mb-10">
        <thead>
        <tr>
            <th class="border-bottom text-left" colspan="2">Vehículos Secundarios</th>
        </tr>
        </thead>
        <tbody>
        @foreach($document->secondary_transports as $row)
        <tr>
            @if($row["plate_number"])
                <td>Número de placa del vehículo: {{ $row["plate_number"] }}</td>
            @endif
            @if($row['tuc'])
                <td>Certificado de habilitación vehicular: {{ $row['tuc'] }}</td>
            @endif
        </tr>
        @endforeach
        </tbody>
    </table>
@endif
@if($document->secondary_drivers)
    <table class="full-width border-box mt-10 mb-10">
        <thead>
        <tr>
            <th class="border-bottom text-left" colspan="3">Conductores Secundarios</th>
        </tr>
        </thead>
        <tbody>
        @foreach($document->secondary_drivers as $row)
        <tr>
            @if($row['name'])
                <td>Conductor: {{$row['name']}}</td>
            @endif
            @if($row['number'])
                <td>Documento: {{ $row['number'] }}</td>
            @endif
            @if($row['license'])
                <td>Licencia: {{ $row['license'] }}</td>
            @endif
        </tr>
        @endforeach
        </tbody>
    </table>
@endif
<table class="full-width border-box mt-10 mb-10">
    <thead class="">
    @if($configuration["enabled_price_items_dispatch"])
    <tr>
        <th class="border-top-bottom text-center">Item</th>
        <th class="border-top-bottom text-center">Código</th>
        <th class="border-top-bottom text-left">Descripción</th>
        <th class="border-top-bottom text-left">Serie</th>
        <th class="border-top-bottom text-left">Modelo</th>
        <th class="border-top-bottom text-center">Unidad</th>
        <th class="border-top-bottom text-center">Cantidad</th>
        <th class="border-top-bottom text-center">Precio</th>
        <th class="border-top-bottom text-right">Total</th>
    </tr>
    @else
    <tr>
        <th class="border-top-bottom text-center">Item</th>
        <th class="border-top-bottom text-center">Código</th>
        <th class="border-top-bottom text-left">Descripción</th>
        <th class="border-top-bottom text-left">Serie</th>
        <th class="border-top-bottom text-left">Modelo</th>
        <th class="border-top-bottom text-center">Unidad</th>
        <th class="border-top-bottom text-right">Cantidad</th>
    </tr>
    @endif
    </thead>
    <tbody>
    @foreach($document->items as $row)
        @if($configuration["enabled_price_items_dispatch"])
            <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td class="text-center">{{ $row->item->internal_id }}</td>
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
                @if($row->relation_item->is_set == 1)
                    <br>
                    @inject('itemSet', 'App\Services\ItemSetService')
                    @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                        {{$item}}<br>
                    @endforeach
                @endif

                @if($document->has_prepayment)
                    <br>
                    *** Pago Anticipado ***
                @endif
            </td>
            {{-- <td class="text-left">
                @php
                    $current_item = $items ? $items->where('item_id', $row->item_id)->first() : null;
                @endphp
                @if($current_item && count($current_item->item->lots) > 0)
                    @foreach($current_item->item->lots as $lot)
                        {{$lot->series}}
                        @if(!$loop->first && $loop->last)
                            -
                        @endif
                    @endforeach
                @endif
            </td> --}}
            <td class="text-left">{{ $row->item->model ?? '' }}</td>
            <td class="text-center">{{ $row->item->unit_type_id }}</td>
            <td class="text-right">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-center">{{ number_format($row->item->unit_price, 2) }}</td>
            <td class="text-right">{{ number_format($row->item->total, 2) }}</td>
        </tr>
        @else 
            <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td class="text-center">{{ $row->item->internal_id }}</td>
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
                @if($row->relation_item->is_set == 1)
                    <br>
                    @inject('itemSet', 'App\Services\ItemSetService')
                    @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                        {{$item}}<br>
                    @endforeach
                @endif

                @if($document->has_prepayment)
                    <br>
                    *** Pago Anticipado ***
                @endif
            </td>
            {{-- <td class="text-left">
                @php
                    $current_item = $items ? $items->where('item_id', $row->item_id)->first() : null;
                @endphp
                @if($current_item && count($current_item->item->lots) > 0)
                    @foreach($current_item->item->lots as $lot)
                        {{$lot->series}}
                        @if(!$loop->first && $loop->last)
                            -
                        @endif
                    @endforeach
                @endif
            </td> --}}
            <td class="text-left">{{ $row->item->model ?? '' }}</td>
            {{-- <td class="text-left">
                @php
                    $current_item = $items ? $items->where('item_id', $row->item_id)->first() : null;
                @endphp
                @if($current_item && count($current_item->item->lots) > 0)
                    @foreach($current_item->item->lots as $lot)
                        {{$lot->series}}
                        @if(!$loop->first && $loop->last)
                            -
                        @endif
                    @endforeach
                @endif
            </td> --}}
            <td class="text-left">{{ $row->item->model ?? '' }}</td>
            <td class="text-center">{{ $row->item->unit_type_id }}</td>
            <td class="text-right">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
         </tr>
        @endif
    @endforeach
    </tbody>
</table>

{{-- @php

$invoice = $document->reference_document()->where('id', $document->reference_document_id)->first();
$items = $invoice ? $invoice->items : null;
@endphp --}}

@if($document->observations)
    <table class="full-width border-box mt-10 mb-10">
        <tr>
            <td class="text-bold border-bottom font-bold">OBSERVACIONES</td>
        </tr>
        <tr>
            <td>{{ $document->observations }}</td>
        </tr>
    </table>
@endif

@if ($document->reference_document)
    <table class="full-width border-box">
        @if($document->reference_document)
            <tr>
                <td class="text-bold border-bottom font-bold">{{$document->reference_document->document_type->description}}</td>
            </tr>
            <tr>
                <td>{{ ($document->reference_document) ? $document->reference_document->number_full : "" }}</td>
            </tr>
        @endif
    </table>
@endif
@if ($document->data_affected_document)
    @php
        $document_data_affected_document = $document->data_affected_document;

    $number = (property_exists($document_data_affected_document,'number'))?$document_data_affected_document->number:null;
    $series = (property_exists($document_data_affected_document,'series'))?$document_data_affected_document->series:null;
    $document_type_id = (property_exists($document_data_affected_document,'document_type_id'))?$document_data_affected_document->document_type_id:null;

    @endphp
    @if($number !== null && $series !== null && $document_type_id !== null)

        @php
            $documentType  = App\Models\Tenant\Catalogs\DocumentType::find($document_type_id);
            $textDocumentType = $documentType->getDescription();
        @endphp
        <table class="full-width border-box">
            <tr>
                <td class="text-bold border-bottom font-bold">{{$textDocumentType}}</td>
            </tr>
            <tr>
                <td>{{$series }}-{{$number}}</td>
            </tr>
        </table>
    @endif
@endif
@if ($document->reference_order_form_id)
    <table class="full-width border-box">
        @if($document->order_form)
            <tr>
                <td class="text-bold border-bottom font-bold">ORDEN DE PEDIDO</td>
            </tr>
            <tr>
                <td>{{ ($document->order_form) ? $document->order_form->number_full : "" }}</td>
            </tr>
        @endif
    </table>

@elseif ($document->order_form_external)
    <table class="full-width border-box">
        <tr>
            <td class="text-bold border-bottom font-bold">ORDEN DE PEDIDO</td>
        </tr>
        <tr>
            <td>{{ $document->order_form_external }}</td>
        </tr>
    </table>

@endif


@if ($document->reference_sale_note_id)
    <table class="full-width border-box">
        @if($document->sale_note)
            <tr>
                <td class="text-bold border-bottom font-bold">NOTA DE VENTA</td>
            </tr>
            <tr>
                <td>{{ ($document->sale_note) ? $document->sale_note->number_full : "" }}</td>
            </tr>
        @endif
    </table>
@endif
@if($document->qr)
<table class="full-width">
    <tr>
        <td class="text-left">
            <img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px;"/>
        </td>
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
