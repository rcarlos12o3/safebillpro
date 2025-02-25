<?php

namespace Modules\MultiUser\Traits\System;

use App\Models\System\Client;
use Hyn\Tenancy\Environment;
use Illuminate\Support\Facades\DB;
use Modules\MultiUser\Models\System\MultiUser;
use App\Models\Tenant\User;
use App\Models\Tenant\ColumnsToReport;
use Exception;


trait MultiUserTrait
{

    /**
     *
     * @param  Client $row
     * @param  array $users
     * @return void
     */
    private function setAllTenantUsers($row, &$users)
    {
        $tenancy = app(Environment::class);
        $tenancy->tenant($row->hostname->website);
        $client_id = $row->id;
        $client_full_name = $row->getFullName();

        DB::connection('tenant')
            ->table('users')
            ->where('is_multi_user', false)
            ->select([
                'id',
                'name',
                'email',
                'establishment_id',
                'type'
            ])
            ->get()
            ->each(function($row) use($users, $client_id, $client_full_name){

                $users->push([
                    'id' => $row->id,
                    'name' => $row->name,
                    'email' => $row->email,
                    'establishment_id' => $row->establishment_id,
                    'type' => $row->type,
                    'client_id' => $client_id,
                    'client_full_name' => $client_full_name,
                    'composed_id' => "{$row->id}-{$client_id}",
                    'full_name' => "{$row->name} - {$row->email}",
                ]);

            });
    }


    /**
     *
     * @return array
     */
    private function getDataMultiUser()
    {
        $users = collect();
        $clients = collect();
        $base_clients = Client::filterDataMultiUser()->get();

        foreach ($base_clients as $client)
        {
            $this->setAllTenantUsers($client, $users);

            $clients->push([
                'id' => $client->id,
                'full_name' => $client->getFullName(),
            ]);
        }

        return compact('clients', 'users');
    }


    /**
     *
     * Gestionar multi usuario
     *
     * @param  MultiUserRequest $request
     * @return array
     */
    private function storeMultiUser($request)
    {
        $composed_id = $this->parseComposedId($request->composed_id);

        $origin_user_id = $composed_id['origin_user_id'];
        $origin_client_id = $composed_id['origin_client_id'];
        $destination_client_id = $request->destination_client_id;

        $exist_user_client = $this->existUserInClient($origin_client_id, $origin_user_id, $destination_client_id);
        if(!$exist_user_client['success']) return $exist_user_client;


        $origin_user = $this->getOriginUser($origin_user_id, $origin_client_id);

        // cambiar conexion a tenant destino
        $this->changeClientConnection($destination_client_id);

        $validate_destination_data = $this->validateDestinationData($request['user']['email']);
        if(!$validate_destination_data['success']) return $validate_destination_data;

        $this->saveClientData($origin_user, $origin_client_id, $request->all());

        return $this->generalResponse(true, 'Acceso registrado correctamente.');
    }


    /**
     *
     * Datos de usuario (cliente origen)
     *
     * @param  int $origin_user_id
     * @param  int $origin_client_id
     * @return User
     */
    private function getOriginUser($origin_user_id, $origin_client_id)
    {
        $this->changeClientConnection($origin_client_id);

        return User::whereFilterWithOutRelations()->findOrFail($origin_user_id);
    }


    /**
     *
     * @param  int $client_id
     * @return void
     */
    private function changeClientConnection($client_id)
    {
        $client = Client::findOrFail($client_id);
        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);
    }


    /**
     *
     * Validar si existe multi usuario registrado
     *
     * @param  int $origin_client_id
     * @param  int $destination_client_id
     * @param  int $origin_user_id
     * @return MultiUser
     */
    private function findDestinationMultiUser($origin_client_id, $destination_client_id, $origin_user_id)
    {
        return MultiUser::where('destination_client_id', $destination_client_id)
                        ->where('origin_user_id', $origin_user_id)
                        ->where('origin_client_id', $origin_client_id)
                        ->select('id')
                        ->first();
    }


    /**
     *
     * @param  string $email
     * @return User
     */
    private function existDestinationClientUser($email)
    {
        return User::whereFilterWithOutRelations()
                    ->where('email', $email)
                    ->select('id')
                    ->first();
    }


    /**
     *
     * @param  string $composed_id
     * @return array
     */
    private function parseComposedId($composed_id)
    {
        $composed_id = explode('-', $composed_id);

        return [
            'origin_user_id' => $composed_id[0],
            'origin_client_id' => $composed_id[1]
        ];
    }


    /**
     *
     * Validar si el usuario existe en cliente destino
     *
     * @param  int $origin_client_id
     * @param  int $origin_user_id
     * @param  int $destination_client_id
     * @return array
     */
    private function existUserInClient($origin_client_id, $origin_user_id, $destination_client_id)
    {
        $message = 'El usuario ya se encuentra registrado en la empresa seleccionada.';

        if($origin_client_id == $destination_client_id) return $this->generalResponse(false, $message);

        $multi_user = $this->findDestinationMultiUser($origin_client_id, $destination_client_id, $origin_user_id);

        if($multi_user) return $this->generalResponse(false, $message);

        return $this->generalResponse(true, null);
    }


    /**
     * Verificar si existe usuario con mismo email en cliente destino
     *
     * @param  string $email
     * @return array
     */
    private function validateDestinationData($email)
    {
        $user_by_email = $this->existDestinationClientUser($email);

        if($user_by_email) return $this->generalResponse(false, 'Existe un usuario con el mismo correo electrónico en la empresa seleccionada.');

        return $this->generalResponse(true, null);
    }


    /**
     *
     * Guardar registros en cliente destino
     *
     * @param  User $origin_user
     * @param  int $origin_client_id
     * @param  array $params
     * @return void
     */
    private function saveClientData($origin_user, $origin_client_id, $params)
    {
        $destination_user = $this->createUserToClient($origin_user);
        $multi_user = $this->createMultiUser($origin_client_id, $origin_user->id, $destination_user->id, $params);

        $destination_user->multi_user_id = $multi_user->id;
        $destination_user->api_token = $origin_user->api_token;
        $destination_user->update();
    }


    /**
     *
     * Obtener primer establecimiento de cliente destino
     *
     * @return Establishment
     */
    public function getFirstEstablishment()
    {
        return DB::connection('tenant')
                ->table('establishments')
                ->select([
                    'id'
                ])
                ->first();
    }


    /**
     *
     * Crear usuario en cliente destino
     *
     * @param  User $origin_user
     * @return User
     */
    public function createUserToClient($origin_user)
    {
        $establishment = $this->getFirstEstablishment();

        return User::create([
            'name' => $origin_user->name,
            'email' => $origin_user->email,
            'password' => $origin_user->password,
            'establishment_id' => $establishment->id,
            'type' => $origin_user->type,
            'is_multi_user' => true
        ]);
    }


    /**
     *
     * @param  int $origin_client_id
     * @param  int $origin_user_id
     * @param  int $destination_user_id
     * @param  array $params
     * @return MultiUser
     */
    public function createMultiUser($origin_client_id, $origin_user_id, $destination_user_id, $params)
    {
        return MultiUser::create([
            'origin_client_id' => $origin_client_id,
            'origin_user_id' => $origin_user_id,
            'destination_client_id' => $params['destination_client_id'],
            'destination_user_id' => $destination_user_id,
            'email' => $params['user']['email'],
            'user' => $params['user']
        ]);
    }


    /**
     *
     * @param  string $message
     * @return void
     */
    public function throwException($message)
    {
        throw new Exception($message);
    }


    /**
     *
     * @param  Exception $exception
     * @param  string $message
     * @return array
     */
    public function parseException($exception, $message)
    {
        $this->generalWriteErrorLog($exception, $message);

        return $this->generalResponse(false, $message.$exception->getMessage());
    }

    public function actionDelete($id)
    {
        $multi = MultiUser::find($id);
        // cambiar conexion a tenant destino
        $this->changeClientConnection($multi->destination_client_id);
        $user = User::whereFilterWithOutRelations()->findOrFail($multi->destination_user_id);
        $tables = [
            'default_document_types',
            'documents',
            'seller_documents',
            'sale_notes',
            'seller_sale_notes',
            'cashes',
            'contracts',
            'devolutions',
            'dispatches',
            'documentary_files',
            'documents_where_seller',
            'expenses',
            'fixed_asset_purchases',
            'global_payments',
            'incomes',
            'items_ratings',
            'order_forms',
            'order_notes',
            'perceptions',
            'purchase_orders',
            'purchase_quotations',
            'purchase_settlements',
            'purchases',
            'quotations',
            'retentions',
            'sale_opportunities',
            'summaries',
            'technical_services',
            'user_commissions',
            'voideds',
            'authorized_discount_users',
            // 'system_activity_logs',
        ];

        $hasRelationship = false;
        $current_table = '';

        foreach ($tables as $table) {
            if ($user->$table()->exists()) {
                $current_table = $table;
                $hasRelationship = true;
                break;
            }
        }

        if ($hasRelationship) {
            // El usuario tiene al menos una relación en una de las tablas
            // Realiza alguna acción aquí
            $message = 'El usuario posee registros en el cliente, no puede ser eliminado. Tabla:'.$current_table;
            return $this->generalResponse(false, $message);
        } else {
            // El usuario no tiene relaciones
            $columns = ColumnsToReport::where('user_id', $user->id)->delete(); // no tiene relacion inversa en modelo
            $user->system_activity_logs()->delete(); // se elimina ya que no hay relación con otras tablas
            $user->delete();
            $multi->delete();
            $message = 'El usuario ha sido eliminado.';
            return $this->generalResponse(true, $message);
        }
    }


}