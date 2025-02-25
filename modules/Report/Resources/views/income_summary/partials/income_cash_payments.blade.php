<table class="">
    <thead>
        <tr>
            <th>#</th>
            <th>Fecha y hora emisión</th>
            <th>Tipo documento</th>
            <th>Documento</th>
            <th>Método de pago</th> 
            <th>Moneda</th>
            <th>Importe</th>
            <th>Vuelto</th>
            <th>Monto</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cash_data['payments'] as $value)
            <tr>
                <td class="celda">{{ $loop->iteration }}</td>
                <td class="celda">{{ $value['date_time_of_issue'] }}</td>
                <td class="celda">{{ $value['document_type_description'] }}</td>
                <td class="celda">{{ $value['number_full'] }}</td>
                <td class="celda">{{ $value['payment_method_type_description'] }}</td>  
                <td class="celda">{{ $value['currency_type_id'] }}</td>  
                <td class="celda">{{ number_format($value['total'], 2) }}</td>
                <td class="celda">{{ number_format($value['change'], 2) }}</td>
                <td class="celda">{{ number_format($value['payment'], 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>