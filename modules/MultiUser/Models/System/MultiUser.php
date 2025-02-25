<?php

namespace Modules\MultiUser\Models\System;

use App\Models\System\Client;


class MultiUser extends ModelSystem
{

    protected $fillable = [
        'origin_client_id',
        'destination_client_id',
        'origin_user_id',
        'destination_user_id',
        'email',
        'user',
    ];

    protected $casts = [
    ];


    public function origin_client()
    {
        return $this->belongsTo(Client::class, 'origin_client_id');
    }

    public function destination_client()
    {
        return $this->belongsTo(Client::class, 'destination_client_id');
    }
    
    public function getUserAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }

    public function setUserAttribute($value)
    {
        $this->attributes['user'] = (is_null($value)) ? null : json_encode($value);
    }

    
    /**
     * 
     * Filtros listado
     *
     * @param  Builder $query
     * @param  Request $request
     * @return Builder
     */
    public function scopeFilterRecords($query, $request)
    {
        $query->select([
            'id',
            'destination_client_id',
            'origin_client_id',
            'email',
            'user',
        ])
        ->with([
            'destination_client' => function($destination_client){
                $destination_client->filterDataMultiUser();
            },
            'origin_client' => function($origin_client){
                $origin_client->filterDataMultiUser();
            }
        ]);

        if(!empty($request->value))
        {
            $query->where($request->column, 'like', "%{$request->value}%");
        }

        return $query;
    }

}
