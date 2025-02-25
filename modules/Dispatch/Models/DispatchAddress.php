<?php

namespace Modules\Dispatch\Models;

use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Person;

class DispatchAddress extends ModelTenant
{
    public $table = 'dispatch_addresses';
    public $timestamps = false;
    protected $with = ['person'];

    protected $fillable = [
        'person_id',
        'address',
        'location_id',
        'is_active',
        'establishment_code'
    ];

    protected $casts = [
        'location_id' => 'array',
        'is_active' => 'boolean',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function scopeWhereFilterRecords($query, $request)
    {
        return $query->generalWhereLikeColumn($request->column, $request->value)
                    ->latest('id');
    }

    public function getResourceRecord()
    {
        return [
            'id' => $this->id,
            'person_id' => $this->person_id,
            'address' => $this->address,
            'location_id' => $this->location_id,
            'is_active' => $this->is_active,
            'establishment_code' => $this->establishment_code,
        ];
    }

    public function getResourceCollection()
    {
        return [
            'id' => $this->id,
            'person_name' => $this->person->name,
            'person_number' => $this->person->number,
            'address' => $this->address,
            'location_name' => $this->location_id,
            'establishment_code' => $this->establishment_code,
        ];
    }
}
