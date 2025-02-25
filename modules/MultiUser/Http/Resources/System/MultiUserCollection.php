<?php

namespace Modules\MultiUser\Http\Resources\System;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Tenant\User;


class MultiUserCollection extends ResourceCollection
{
    
    public function toArray($request) 
    {
        return $this->collection->transform(function($row, $key){

            $client_destination_full_name = 'Cliente eliminado';
            $destination_hostname = 'Subdominio no encontrado';

            if($row->destination_client)
            {
                $client_destination_full_name = $row->destination_client->getFullName();
                $destination_hostname = $row->destination_client->hostname->fqdn;
            }

            $client_origin_full_name = 'Cliente eliminado';
            $origin_hostname = 'Subdominio no encontrado';

            if($row->origin_client)
            {
                $client_origin_full_name = $row->origin_client->getFullName();
                $origin_hostname = $row->origin_client->hostname->fqdn;
            }

            return [
                'id' => $row->id,
                'client_destination_full_name' => $client_destination_full_name,
                'origin_hostname' => $origin_hostname,
                'client_origin_full_name' => $client_origin_full_name,
                'destination_hostname' => $destination_hostname,
                'user_full_name' => $row->email,
                'description_type' => User::getDescriptionType($row->user->type)
            ];
        });
    }
}
