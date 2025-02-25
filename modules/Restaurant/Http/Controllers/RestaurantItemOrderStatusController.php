<?php

namespace Modules\Restaurant\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Exception;
use App\Models\Tenant\Item;
use Modules\Restaurant\Models\RestaurantItemOrderStatus;


class RestaurantItemOrderStatusController extends Controller
{
    const STATUS_RECEIVED = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_TO_DELIVER = 3;
    const STATUS_DELIVERED = 4;

    public function saveItemOrder(Request $request) {

        $orderStatus = new RestaurantItemOrderStatus();
        $orderStatus->table_id = $request->table_id;
        $orderStatus->item_id = $request->item_id;
        $orderStatus->item = json_encode($request->item);
        $orderStatus->quantity = $request->quantity;
        $orderStatus->note = $request->note;
        $orderStatus->status = $request->status;
        $orderStatus->status_description = $request->status_description;
        $orderStatus->save();

        return [
            'success' => true,
            'message' => 'Producto agregado con éxito.'
        ];
        
    }

    public function getStatusItems($id)
    {
        $data = [
            'productsStatusReceived' => $this->getItemsByStatus(self::STATUS_RECEIVED,$id),
            'productsStatusProcessing' => $this->getItemsByStatus(self::STATUS_PROCESSING,$id),
            'productsStatusToDeliver' => $this->getItemsByStatus(self::STATUS_TO_DELIVER,$id),
            'productsStatusDelivered' => $this->getItemsByStatus(self::STATUS_DELIVERED,$id, 20,'desc'),
        ];

        return [
            'success' => true,
            'data' => $data,
            'message' => 'Listado de productos por estados.',
            'id' =>$id
        ];
    }

    private function getItemsByStatus($status, $table_id = 0, $limit = null, $desc = null)
    {
        $query = RestaurantItemOrderStatus::where('status', $status)
            ->with(['table']);

        if ($table_id>0) {
            $query->where('table_id',$table_id);
        }

        if ($limit) {
            $query->take($limit);
        }

        if ($desc) {
            $query->orderBy('updated_at',$desc);
        }

        return $query->get()->transform(function ($order) {
            return $this->transformOrderData($order);
        });
    }

    private function transformOrderData($order)
    {
        $itemData = json_decode($order->item);

        return [
            'id' => $order->id,
            'name' => $itemData->name ?? null,
            'quantity' => $order->quantity,
            'note' => $order->note ?? null,
            'status' => $order->status,
            'status_description' => $order->status_description,
            'mesa_id' => $order->table_id,
            'mesa' => $order->table->label ?? null,
            'environment_id' => $order->table->environment_id ?? null,
            'environment' => $order->table->environment ?? null,
        ];
    }

    public function setStatusItem($id)
    {
        $order = RestaurantItemOrderStatus::where('id', $id)->first();
        if($order->status < 4){
            $order->status += 1; 
        }
        $order->save();
        
        return [
            'success' => ($order)?true:false,
            'message' => 'Estado cambiado con éxito'
        ];
    }

}
