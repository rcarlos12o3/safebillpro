<?php

namespace Modules\Hotel\Models;

use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Establishment;

class HotelCategory extends ModelTenant
{
	protected $table = 'hotel_categories';

	protected $fillable = [
		'description',
		'active',
		'establishment_id',
	];

	public function getActiveAttribute($value)
	{
		return $value ? true : false;
	}

	public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

}
