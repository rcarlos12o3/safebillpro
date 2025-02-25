<?php

namespace Modules\Restaurant\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Restaurant\Models\RestaurantConfiguration;
use Modules\Restaurant\Models\RestaurantRole;
use Modules\Restaurant\Models\RestaurantTable;
use Modules\Restaurant\Models\RestaurantTableEnv;
use App\Models\Tenant\User;
use App\Models\Tenant\Company;
use Modules\Restaurant\Models\RestaurantItemOrderStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Tenant\UserResource;


class RestaurantConfigurationController extends Controller
{
    /**
     * muestra vista para utilizar en mozo
     */
    public function configuration()
    {
        return view('restaurant::configuration.index');
    }

    /**
     * obtiene configuración para utilizar en mozo
     */
    public function record()
    {
        $configurations = RestaurantConfiguration::first();
        $company = Company::query()->first();
        $user = auth()->user();

        return [
            'success' => true,
            'data' => $configurations->getCollectionData(),
            'info' => ['ruc' => $company->number, 'userEmail' => $user->email, 'socketServer' => config('tenant.socket_server') ?? 'http://localhost:8070'],
        ];
    }

    public function tablesAndEnv()
    {
        $tables = RestaurantTable::whereNotNull('environment')->get()->transform(function ($row) {
            return (object)[
                'id' => $row->id,
                'status' => $row->status,
                'products' => (array)$row->products,
                'total' => (float)$row->total,
                'personas' => $row->personas,
                'label' => $row->label,
                'shape' => $row->shape,
                'environment' => $row->environment,
                'open' => false,
                'close' => false,
                'quantityOrders' => (count((array)$row->products)>0)?$this->getQuantityOrdersByTable((array)$row->products):0,
                'timeOpening' => ($row->opening_date)?$this->getTimeByDateOpening($row->opening_date):null,
            ];
        });

        $configuration = RestaurantConfiguration::first();

        return [
            'tables' => $tables,
            'enabled_environment_1' => (object)['active' => (bool)$configuration->enabled_environment_1, 'tablesQuantity' => $configuration->tables_quantity,'name'=> RestaurantTableEnv::where('id', 1)->pluck('name')->first()],
            'enabled_environment_2' => (object)['active' => (bool)$configuration->enabled_environment_2, 'tablesQuantity' => $configuration->tables_quantity_environment_2,'name'=> RestaurantTableEnv::where('id', 2)->pluck('name')->first()],
            'enabled_environment_3' => (object)['active' => (bool)$configuration->enabled_environment_3, 'tablesQuantity' => $configuration->tables_quantity_environment_3,'name'=> RestaurantTableEnv::where('id', 3)->pluck('name')->first()],
            'enabled_environment_4' => (object)['active' => (bool)$configuration->enabled_environment_4, 'tablesQuantity' => $configuration->tables_quantity_environment_4,'name'=> RestaurantTableEnv::where('id', 4)->pluck('name')->first()],
        ];
    }

    private function getTimeByDateOpening($date_opening = null)
    {
        if($date_opening){
            $date = $date_opening;
            $dateOpening = Carbon::parse($date);
            $now = Carbon::now();

            $diffHours = $dateOpening->diffInHours($now);
            $diffMinutes = $dateOpening->diffInMinutes($now) % 60;
    
            if ($diffHours > 0) {
                $result = $diffHours.' h'. ($diffHours > 1 ? 's' : '').' y '.$diffMinutes.' min';
            } else {
                $result = $diffMinutes.' min';
            }

            return $result;
        }
        return null ;
    }


    private function getQuantityOrdersByTable($items)
    {
        $quantityOrders = collect($items)->sum('quantity');
        return $quantityOrders;
    }

    /**
     * guarda cada nueva configuración para utilizar en mozo
     */
    public function setConfiguration(Request $request)
    {
        $configuration = RestaurantConfiguration::first();
        $configuration->fill($request->all());
        if (!$configuration->menu_pos && !$configuration->menu_order && !$configuration->menu_tables) {
            $configuration->menu_pos = true;
        }
        $configuration->save();

        $this->generateMesas($request);

        return [
            'success' => true,
            'configuration' => $configuration->getCollectionData(),
            'message' => 'Configuración actualizada',
        ];
    }

    private function generateMesas($request)
    {
        $enabled_environment_1 = (bool)$request->enabled_environment_1;
        $enabled_environment_2 = (bool)$request->enabled_environment_2;
        $enabled_environment_3 = (bool)$request->enabled_environment_3;
        $enabled_environment_4 = (bool)$request->enabled_environment_4;

        $tables_quantity_environment_1 = (int)$request->tables_quantity;
        $tables_quantity_environment_2 = (int)$request->tables_quantity_environment_2;
        $tables_quantity_environment_3 = (int)$request->tables_quantity_environment_3;
        $tables_quantity_environment_4 = (int)$request->tables_quantity_environment_4;

        RestaurantTable::truncate();
        RestaurantItemOrderStatus::truncate();

        //create env 1
        if ($enabled_environment_1) {
            for ($i = 1; $i <= $tables_quantity_environment_1; $i++) {
                RestaurantTable::create([
                    'status' => 'available',
                    'products' => array(),
                    'total' => 0,
                    'personas' => 1,
                    'label' => strval($i),
                    'shape' => 'CUADRADO',
                    'environment' => RestaurantTableEnv::where('id', 1)->pluck('name')->first(),
                ]);
            }
        }

        //create env 2
        if ($enabled_environment_2) {
            for ($i = 1; $i <= $tables_quantity_environment_2; $i++) {
                RestaurantTable::create([
                    'status' => 'available',
                    'products' => [],
                    'total' => 0,
                    'personas' => 1,
                    'label' => strval($i),
                    'shape' => 'CUADRADO',
                    'environment' => RestaurantTableEnv::where('id', 2)->pluck('name')->first(),
                ]);
            }
        }

        //create env 3
        if ($enabled_environment_3) {
            for ($i = 1; $i <= $tables_quantity_environment_3; $i++) {
                RestaurantTable::create([
                    'status' => 'available',
                    'products' => [],
                    'total' => 0,
                    'personas' => 1,
                    'label' => strval($i),
                    'shape' => 'CUADRADO',
                    'environment' => RestaurantTableEnv::where('id', 3)->pluck('name')->first(),
                ]);
            }
        }


        //create env 4
        if ($enabled_environment_4) {
            for ($i = 1; $i <= $tables_quantity_environment_4; $i++) {
                RestaurantTable::create([
                    'status' => 'available',
                    'products' => [],
                    'total' => 0,
                    'personas' => 1,
                    'label' => strval($i),
                    'shape' => 'CUADRADO',
                    'environment' => RestaurantTableEnv::where('id', 4)->pluck('name')->first(),
                ]);
            }
        }
    }

    /**
     * consulta los roles actuales
     */
    public function getRoles()
    {
        $roles = RestaurantRole::orderBy('name', 'ASC')->get();
        $alls = $roles->transform(function ($item) {
            return $item->getCollectionData();
        });

        return [
            'success' => true,
            'data' => $alls
        ];
    }

    /**
     * consulta los usuarios actuales
     */
    public function getUsers()
    {
        $users = User::orderBy('name')->where('type','<>','client')->get();
        $alls = $users->transform(function ($item) {
            return $item->getCollectionRestaurantData();
        });

        return [
            'success' => true,
            'data' => $alls
        ];
    }

    /**
     * asigna o actualiza un rol a un usuario
     */
    public function setRole(Request $request)
    {
        $user = User::find($request->user_id);
        $user->restaurant_role_id = $request->role_id;
        $user->save();

        return [
            'success' => true,
            'message' => 'Rol asignado a usuario exitosamente',
        ];
    }

    public function deleteRole(Request $request)
    {
        $user = User::find($request->user_id);
        $user->restaurant_role_id = null;
        $user->save();

        return [
            'success' => true,
            'message' => 'Rol quitado a usuario exitosamente',
        ];
    }

    public function saveTable($id, Request $request)
    {
        $table = RestaurantTable::findOrFail($id);
        $data = $request->all();
        $data['status'] = (count($data['products'])<1)?$data['status']:'notavailable';

        if(isset($data['open'])&& $data['open']){
            $data['opening_date'] = Carbon::now();
        }

        if(isset($data['close'])&& $data['close']){
            $data['opening_date'] = null;
        }

        $table->fill($data);
        $table->save();

        if(count($data['products']) < 1){
            $itemsToDelete = RestaurantItemOrderStatus::where('table_id', $id);
            if ($itemsToDelete->exists()) {
                $itemsToDelete->delete();
            }
        }

        return [
            'success' => true,
            'message' => 'Mesa actualizada.',
        ];
    }

    public function saveLabelTable(Request $request)
    {
        $table = RestaurantTable::findOrFail($request->id);
        $table->label = $request->label ;
        $table->shape = $request->shape ;
        $table->save();
        return [
            'success' => true,
            'message' => 'Mesa actualizada con éxito.',
        ];
    }

    public function getTable($id)
    {
        $row = RestaurantTable::findOrFail($id);

        $table = (object)[
            'id' => $row->id,
            'status' => $row->status,
            'products' => (array)$row->products,
            'total' => (float)$row->total,
            'personas' => $row->personas,
            'label' => $row->label,
            'shape' => $row->shape,
            'environment' => $row->environment,
        ];

        return compact('table');
    }

    public function getSellers()
    {
        $users = User::where('active', 1)
            ->where('restaurant_role_id','<>',null)
            ->select('id','name','email')
            ->get();
        
        return [
            'success' => true,
            'message' => 'Vendedores disponibles',
            'data' => ($users)?$users:[]
        ];
    }

    public function correctPinCheck($id,$pin)
    {
        $user = User::where('active', 1)
            ->where('id',$id)
            ->where('restaurant_pin',$pin)
            ->select('id','name','email')
            ->first();
        
        return [
            'success' => ($user)?true:false,
            'message' => 'Verificar pin',
            'data' => ($user)?$user:[]
        ];
    }

    public function userStore(Request $request) 
    {
        $id = $request->input('id');
        $email = $request->input('email');
        if (!$id && !$email) { 
            $milliseconds = round(microtime(true) * 1000);
            $email = $milliseconds.'@gmail.com';
        }

        if (!$id) { 
            $verify = User::where('email', $email)->first();
            if ($verify) {
                return [
                    'success' => false,
                    'message' => 'Email no disponible. Ingrese otro Email'
                ];
            }
        }

        DB::connection('tenant')->transaction(function () use ($request, $id, $email) {

            $user = User::firstOrNew(['id' => $id]);
            $user->name = $request->input('name');
            $user->email = $email;

            if($request->input('restaurant_pin')){
                $user->restaurant_pin = $request->input('restaurant_pin');
            }

            if (!$id){
                $user->type = 'seller';
            }
            
            if (!$id && $request->restaurant_role_id != 1 && $request->restaurant_role_id != 6) {
                $user->api_token = str_random(50);
                $password = $request->input('password');
                $user->password = bcrypt($password);
            }

            if($request->restaurant_role_id === 1|| $request->restaurant_role_id === 6){
                $password = $request->input('restaurant_pin');
                $user->password = bcrypt($password);
                if (!$id){
                    $user->api_token = str_random(50);
                }
            }elseif ($request->has('password')) {
                if (config('tenant.password_change')) {
                    $user->password = bcrypt($request->input('password'));
                }
            }

            $user->establishment_id = ($request->input('establishment_id'))?$request->input('establishment_id'):1;
            $user->restaurant_role_id = $request->input('restaurant_role_id');
            $user->save();
            
        });

        return [
            'success' => true,
            'message' => ($id) ? 'Usuario actualizado' : 'Usuario registrado'
        ];
    }

    public function userRecord($id)
    {
        $record = new UserResource(User::findOrFail($id));

        return $record;
    }

    public function getEnvs()
    {
        $envs = RestaurantTableEnv::get()->transform(function ($item) {
            return [
                'name' => $item->name,
                'original_name' => $item->name,
                'enabled_edit' => false
            ];
        });

        return [
            'success' => true,
            'data' => [
                'environment_1'=> $envs[0],
                'environment_2'=> $envs[1],
                'environment_3'=> $envs[2],
                'environment_4'=> $envs[3]
            ]
        ];
    }

    public function updateTableEnv(Request $request)
    {
        $tableEnvFound = RestaurantTableEnv::where('name',$request->name)->where('id','<>',$request->id)->first();

        if($tableEnvFound){
            return [
                'success' => false,
                'message' => 'Nombre ya existe.',
            ];
        }

        $tableEnv = RestaurantTableEnv::findOrFail($request->id);

        RestaurantTable::where('environment', $tableEnv->name)->update(['environment' => $request->name]);

        $tableEnv->name = $request->name ;
        $tableEnv->save();

        

        return [
            'success' => true,
            'message' => 'Nombre actualizado con éxito.',
        ];
    }

}
