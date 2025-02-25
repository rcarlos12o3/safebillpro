<?php

namespace App\Models\Tenant;

use Hyn\Tenancy\Traits\UsesTenantConnection;

class TemporaryKardexRecord extends ModelTenant
{
    use UsesTenantConnection;
    
    protected $table = 'temporary_kardex_records';

    protected $fillable = [
        'inventory_kardex_id',
        'item_name',
        'date_time',
        'date_of_issue',
        'number',
        'sale_note_asoc',
        'order_note_asoc',
        'doc_asoc',
        'inventory_kardexable_type',
        'item_warehouse_price',
        'warehouse',
        'input',
        'output',
        'balance',
        'type_transaction',
        'date_of_register',
        'guide_id',
    ];
}
