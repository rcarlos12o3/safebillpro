<table>
    <tr>
        <th>
            Vou.Origen
        </th>
        <th>
            Vou.Numero
        </th>
        <th>
            Vou.Fecha
        </th>
        <th>
            Doc
        </th>
        <th>
            Numero
        </th>
        <th>
            Fec.Doc
        </th>
        <th>
            Fec.Venc.
        </th>
        <th>
            Codigo
        </th>
        <th>
            Valor Exp.
        </th>
        <th>
            B.Imponible
        </th>
        <th>
            Inafecto
        </th>
        <th>
            Exonerado
        </th>
        <th>
            I.S.C.
        </th>
        <th>
            IGV
        </th>
        <th>
            OTROS TRIB.
        </th>
        <th>
            IMP. BOLSA
        </th>
        <th>
            Moneda
        </th>
        <th>
            TC
        </th>
        <th>
            Glosa
        </th>
        <th>
            Cta Ingreso
        </th>
        <th>
            Cta IGV
        </th>
        <th>
            Cta O. Trib.
        </th>
        <th>
            Cta x Cobrar
        </th>
        <th>
            C.Costo
        </th>
        <th>
            Presupuesto
        </th>
        <th>
            R.Doc
        </th>
        <th>
            R.numero 
        </th>
        <th>
            R.Fecha
        </th>
        <th>
            RUC
        </th>
        <th>
            R.Social
        </th>
        <th>
            Tipo
        </th>
        <th>
            Tip.Doc.Iden
        </th>
        <th>
            Medio de Pago
        </th>
        <th>
            Apellido 1
        </th>
        <th>
            Apellido 2
        </th>
        <th>
            Nombre
        </th>
        <th>
            P.origen
        </th>
        <th>
            P.vou
        </th>
        <th>
            P.fecha
        </th>
        <th>
            P.fecha D.
        </th>
        <th>
            P.fecha V.
        </th>
        <th>
            P.cta cob
        </th>
        <th>
            P.m.pago
        </th>
        <th>
            P.doc
        </th>
        <th>
            P.num doc
        </th>
        <th>
            P.moneda
        </th>
        <th>
            P.tc
        </th>
        <th>
            P.monto
        </th>
        <th>
            P.glosa
        </th>
        <th>
            P.fe
        </th>
        <th>
            Retencion 0/1
        </th>
        <th>
            PDB ndes
        </th>
        <th>
            CodTasa
        </th>
        <th>
            Ind.Ret
        </th>
        <th>
            B.Imp	
        </th>
        <th>
            IGV
        </th>
    </tr>
    @foreach($records as $row)
        <tr>
            <td>02</td>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $row['date_of_issue'] }}</td>
            <td>{{ $row['document_type_id'] }}</td>
            <td>{{ $row['number_full'] }}</td>
            <td>{{ $row['date_of_issue'] }}</td>
            <td>{{ $row['date_of_due'] }}</td>
            <td>{{ $row['customer_number'] }}</td>
            <td>{{ $row['total_exportation'] }}</td>
            <td>{{ $row['total_taxed'] }}</td>
            <td>{{ $row['total_unaffected'] }}</td>
            <td>{{ $row['total_exonerated'] }}</td>
            <td>{{ $row['total_isc'] }}</td>
            <td>{{ $row['total_igv'] }}</td>
            <td></td>
            <td>{{ $row['total_plastic_bag_taxes'] }}</td>
            <td>{{ $row['currency_type_id'] }}</td>
            <td>{{ $row['exchange_rate_sale'] }}</td>
            <td>VENTA</td>
            <td>{{ $row['income_account'] }}</td>
            <td>{{ $row['igv_account'] }}</td>
            <td></td>
            <td>{{ $row['receivable'] }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ $row['customer_number'] }}</td>
            <td>{{ $row['customer_name'] }}</td>
            <td>2</td>
            <td>{{ $row['customer_identity_document_type_id'] }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>04</td>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $row['date_of_issue'] }}</td>
            <td>{{ $row['date_of_issue'] }}</td>
            <td>{{ $row['date_of_due'] }}</td>
            <td>1011</td>
            <td>008</td>
            <td></td>
            <td>{{ $row['number_full'] }}</td>
            <td>{{ $row['currency_type_id'] }}</td>
            <td>{{ $row['exchange_rate_sale'] }}</td>
            <td>{{ $row['total'] }}</td>
            <td>VENTA</td>
            <td>O101</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    @endforeach
</table>
