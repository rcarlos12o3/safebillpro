<?php

namespace Modules\MultiUser\Http\Controllers\System;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\MultiUser\Http\Resources\System\MultiUserCollection;
use Modules\MultiUser\Models\System\MultiUser;
use Modules\MultiUser\Traits\System\MultiUserTrait;
use Modules\MultiUser\Http\Requests\System\MultiUserRequest;
use Illuminate\Support\Facades\DB;
use Exception;


class MultiUserController extends Controller
{

    use MultiUserTrait;

    /**
     * @return Response
     */
    public function index()
    {
        return view('multiuser::system.multi-users.index');
    }


    /**
     *
     * @return array
     */
    public function columns()
    {
        return [
            'email' => 'Correo electrónico',
        ];
    }


    /**
     *
     * @return array
     */
    public function tables()
    {
        return $this->getDataMultiUser();
    }


    /**
     *
     * @param  Request $request
     * @return MultiUserCollection
     */
    public function records(Request $request)
    {
        $records = MultiUser::filterRecords($request)->latest();

        return new MultiUserCollection($records->paginate(config('tenant.items_per_page')));
    }


    /**
     *
     * @param  MultiUserRequest $request
     * @return array
     */
    public function store(MultiUserRequest $request)
    {
        try
        {
            return DB::transaction(function () use ($request) {
                return $this->storeMultiUser($request);
            });
        }
        catch(Exception $e)
        {
            return $this->parseException($e, 'Ocurrió un error desconocido: ');
        }
    }

    public function delete(Request $request) {

        return $this->actionDelete($request->id);
    }
}
