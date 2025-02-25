<?php

namespace Modules\Restaurant\Models;

use App\Models\Tenant\ModelTenant;
use Modules\Restaurant\Models\RestaurantTable;

class RestaurantItemOrderStatus extends ModelTenant
{

    protected $fillable = [
        'table_id',
        'item_id',
        'item',
        'quantity',
        'note',
        'status',
        'status_description'
    ];
    
    public function table()
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

}
