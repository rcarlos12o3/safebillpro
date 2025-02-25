<?php

namespace Modules\Purchase\Models;

use App\Models\Tenant\{
    Item,
    ModelTenant
};
use Illuminate\Database\Eloquent\Relations\MorphTo;


class WeightedAverageCost extends ModelTenant
{

    protected $fillable = [
        'origin_id',
        'origin_type',
        'item_id',
        'date_of_issue',
        'quantity',
        'cost',
        'total',
        'weighted_cost',
        'stock',
    ];

    
    protected $casts = [
        'quantity' => 'float',
        'cost' => 'float',
        'total' => 'float',
        'weighted_cost' => 'float',
        'stock' => 'float',
    ];


    public function item()
    {
        return $this->belongsTo(Item::class);
    }


    /**
     * @return MorphTo
     */
    public function origin()
    {
        return $this->morphTo();
    }

    
    /**
     *
     * @param  int $item_id
     * @param  bool $only_weighted_cost
     * @return WeightedAverageCost
     */
    public static function findLastWeightedAverageCost($item_id, $only_weighted_cost = false)
    {
        $query = self::where('item_id', $item_id)->orderForLastRow();

        if($only_weighted_cost) $query->select(['weighted_cost']);

        return $query->first();
    }


    /**
     * 
     * Orden para obtener el ultimo registro
     *
     * @param  Builder $query
     * @return Builder
     */
    public function scopeOrderForLastRow($query)
    {
        return $query->orderBy('id', 'desc');
    }

}
