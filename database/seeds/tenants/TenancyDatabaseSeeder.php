<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class TenancyDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // $id = DB::table('items')->insertGetId(
        //     ['name' => 'Laptop Razer', 'second_name' => 'Laptop Razer', 'description' => 'Laptop Razer','item_type_id' => '01', 
        //     'unit_type_id' => 'NIU', 'currency_type_id' => 'PEN', 'sale_unit_price' => '5500.00', 'has_igv' => 1, 'purchase_unit_price' => '3000.00', 'has_isc' => 0,
        //     'amount_plastic_bag_taxes' => '0.10', 'percentage_isc' => '0.00', 'suggested_price' => '0.00', 'sale_affectation_igv_type_id' => '10' , 
        //     'purchase_affectation_igv_type_id' => '10', 'calculate_quantity' => '0', 'image' => 'demo1.jpg', 'image_medium' => 'demo1_medium.jpg', 'image_small' => 'demo1_small.jpg',  'stock' => '1',
        //     'stock_min' => '1',  'percentage_of_profit' => '20.0',  'has_perception' => '0',  'status' => 1, 'apply_store' => 1 ]
        // );

        // $id2 = DB::table('items')->insertGetId(
           
        //     ['name' => 'MacBook Pro', 'second_name' => 'LMacBook Pro', 'description' => 'MacBook Pro','item_type_id' => '01', 
        //     'unit_type_id' => 'NIU', 'currency_type_id' => 'PEN', 'sale_unit_price' => '5500.00', 'has_igv' => 1, 'purchase_unit_price' => '3000.00', 'has_isc' => 0,
        //     'amount_plastic_bag_taxes' => '0.10', 'percentage_isc' => '0.00', 'suggested_price' => '0.00', 'sale_affectation_igv_type_id' => '10' , 
        //     'purchase_affectation_igv_type_id' => '10', 'calculate_quantity' => '0', 'image' => 'demo2.jpg', 'image_medium' => 'demo2_medium.jpg', 'image_small' => 'demo2_small.jpg',  'stock' => '1',
        //     'stock_min' => '1',  'percentage_of_profit' => '20.0',  'has_perception' => '0',  'status' => 1, 'apply_store' => 1 ]
        // );

        // $id3 = DB::table('items')->insertGetId(
           
        //     ['name' => 'Laptop Asus', 'second_name' => 'Laptop Asus', 'description' => 'Laptop Asus','item_type_id' => '01', 
        //     'unit_type_id' => 'NIU', 'currency_type_id' => 'PEN', 'sale_unit_price' => '5500.00', 'has_igv' => 1, 'purchase_unit_price' => '3000.00', 'has_isc' => 0,
        //     'amount_plastic_bag_taxes' => '0.10', 'percentage_isc' => '0.00', 'suggested_price' => '0.00', 'sale_affectation_igv_type_id' => '10' , 
        //     'purchase_affectation_igv_type_id' => '10', 'calculate_quantity' => '0', 'image' => 'demo3.jpg', 'image_medium' => 'demo3_medium.jpg', 'image_small' => 'demo3_small.jpg',  'stock' => '1',
        //     'stock_min' => '1',  'percentage_of_profit' => '20.0',  'has_perception' => '0',  'status' => 1, 'apply_store' => 1 ]
         
        // );

        // DB::table('promotions')->insert([
        //     ['name' => 'Promocion 1', 'description' => 'Promocion 1', 'image' => 'promo1.jpg', 'item_id'=> $id, 'status'=> 1 ],
        //     ['name' => 'Promocion 2', 'description' => 'Promocion 2', 'image' => 'promo2.jpg', 'item_id'=> $id2, 'status'=> 1 ],
        //     ['name' => 'Promocion 3', 'description' => 'Promocion 3', 'image' => 'promo3.jpg', 'item_id'=> $id3, 'status'=> 1 ]
        // ]);

        // DB::table('module_user')->insert([
        //     ['module_id' => 10, 'user_id' => 1, ]
        // ]);
        if (DB::table('format_templates')->get()->count() == 0) {
            DB::table('format_templates')->insert([
                ['id'=> 1, 'formats' => 'con_valor_unitario'],
                ['id'=> 2, 'formats' => 'default'],
                ['id'=> 3, 'formats' => 'default2'],
                ['id'=> 4, 'formats' => 'font_sm'],
                ['id'=> 5, 'formats' => 'font_sw2'],
                ['id'=> 6, 'formats' => 'legend_amazonia'],
                ['id'=> 7, 'formats' => 'model1'],
                ['id'=> 8, 'formats' => 'model2'],
                ['id'=> 9, 'formats' => 'model3'],
                ['id'=> 10, 'formats' => 'model4'],
                ['id'=> 11, 'formats' => 'modelw80'],
                ['id'=> 12, 'formats' => 'santiago'],
                ['id'=> 13, 'formats' => 'top_placa'],
                ['id'=> 14, 'formats' => 'unit_types_desc']
            ]);
        }

        //! Rellenar los warehouse_id desde inventories hasta items

        $items_warehouse_null = DB::table('items')->where("warehouse_id", '=', null)->get();

        $items_warehouse_null->each(function($item, $key){
            $inventory_warehouse_id = DB::table('inventories')->where('item_id', $item->id)->first();
            if ($inventory_warehouse_id) {
                DB::table('items')->where('id', '=',$item->id )->update([
                    "warehouse_id" => $inventory_warehouse_id->warehouse_id
                ]);
            }
        });
        

        /**
         * ! Mandar los datos de DispatchAddress a PersonAddress
         */
        
        $dispatch_address_data = DB::table("dispatch_addresses")->get();
        
        if ($dispatch_address_data->count() > 0) {

            // Siempre va a validar cuando ya un tenant exista y además si tiene valores dentro de la tabla dispatch_addresses
            $configurations = DB::table("configurations")->first();
            if ($configurations->is_migrated_address) {
                return; 
            }


            $data_insert_all = collect([]);

            // Se crea un nuevo array pero en la llave location_id, en vez de tener los valors en json, se tendra un array con todo los valores parseados
            $dispatch_address_data->transform(function ($item, $key)  {

                //Convierto el objeto estandar en un array asociativo
                $objec_dispatch_addresses = collect( (array) $item);

                $parse_item = $objec_dispatch_addresses->map(function($item_object, $key_object){
                    if ($key_object == "location_id") {
                        // |  country_id |  department_id(0)  | province_id)(1)  | district_id(2)  |
                        $location_clear = json_decode($item_object);

                        $ubigeo_deparment = DB::table('departments')->get();
                        $ubigeo_province = DB::table('provinces')->get();
                        $ubigeo_district = DB::table('districts')->get();

                        // Encontrar si tiene un id valido en la los datos ubigeo

                        $deparment_find = $ubigeo_deparment->where("id", $location_clear[0]);
                        $province_find = $ubigeo_province->where("id", $location_clear[1]);
                        $district_find = $ubigeo_district->where("id", $location_clear[2]);

                        // Si no es un caracter valido dentro de las base de datos, entonces se devolverá un null
                        $deparment = $deparment_find->count() == 0 ? null : $location_clear[0];
                        $province = $province_find->count() == 0 ? null : $location_clear[1];
                        $district = $district_find->count() == 0 ? null : $location_clear[2];

                        return ['PE', $deparment ,$province, $district ];
                    } else {
                        return $item_object;
                    } 
                });

                return $parse_item->all(); 

            });

            // Tabla de person address 
            // | person_id | country_id (PE) | department_id | province_id | district_id | address | phone | email | main | establishment_code 
            
            $array_dispatch_data = $dispatch_address_data->all();
           foreach ($array_dispatch_data as $data_dispatch) {
             $array_data_insert_one = [];

             //person_id 
             $array_data_insert_one["person_id"] = $data_dispatch["person_id"];

             //country_id
             $array_data_insert_one["country_id"] = $data_dispatch["location_id"][0];

             //department_id
             $array_data_insert_one["department_id"] = $data_dispatch["location_id"][1];

             //province_id
             $array_data_insert_one["province_id"] = $data_dispatch["location_id"][2];

             //district_id
             $array_data_insert_one["district_id"] = $data_dispatch["location_id"][3];

             //address
             $array_data_insert_one["address"] = $data_dispatch["address"];

             //phone
             $array_data_insert_one["phone"] = null;

             // TODO  investigar que va en main 
             $array_data_insert_one["main"] = 0;

             //establishment_code 
             $array_data_insert_one["establishment_code"] = $data_dispatch["establishment_code"];

             $data_insert_all->push($array_data_insert_one);
           } 

            DB::table('person_addresses')->insert($data_insert_all->all());

            DB::table('configurations')->update([
                'is_migrated_address' => true
            ]);
         }

    }
}
