<?php

namespace App\Models\Tenant\Catalogs;

use App\Models\Tenant\Item;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Builder;

class UnitType extends ModelCatalog
{
    use UsesTenantConnection;
    
    protected $table = "cat_unit_types";
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'active',
        'symbol',
        'description',
    ];

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope('active', function (Builder $builder) {
    //         $builder->where('active', 1);
    //     });
    // }
    public function items()
    {
        return $this->hasMany(Item::class, 'unit_type_id');
    }

    public function item_unit_types()
    {
        return $this->hasMany(ItemUnitType::class);
    }
}