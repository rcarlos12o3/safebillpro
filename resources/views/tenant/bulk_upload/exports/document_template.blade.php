<table>
    <thead>
        <tr>
            <th style="background-color: #4472C4; color: white; font-weight: bold;">ITEM_ID</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold;">PRODUCTO</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold;">PRECIO_UNIT</th>
            <th style="background-color: #FFD966; color: black; font-weight: bold;">SERIE</th>
            <th style="background-color: #FFD966; color: black; font-weight: bold;">CUSTOMER_ID</th>
            <th style="background-color: #FFD966; color: black; font-weight: bold;">CANTIDAD</th>
            <th style="background-color: #A9D08E; color: black; font-weight: bold;">TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->description }}</td>
            <td>{{ $item->sale_unit_price }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        @endforeach
    </tbody>
</table>
