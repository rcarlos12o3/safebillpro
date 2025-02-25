@php
    $path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $configurationInPdf= App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationInPdf();
@endphp
<head>
    <link href="{{ $path_style }}" rel="stylesheet" />
</head>
<body>
<table class="full-width">
    <tr>
        <td class="text-center desc font-bold">
            @if($configurationInPdf->legend_footer_sale)
                {!! $configurationInPdf->legend_footer_sale !!}
            @endif
            Para consultar el comprobante ingresar a {!! url('/buscar') !!}
            <br>
            @if($document && $document->document_type)
                Representacion impresa de la <span style="text-transform: capitalize" class="text-capitalize">{{ $document->document_type->description }}</span>
            @endif
        </td>
    </tr>
</table>
</body>