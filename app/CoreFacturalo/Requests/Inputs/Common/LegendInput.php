<?php

namespace App\CoreFacturalo\Requests\Inputs\Common;

use App\CoreFacturalo\Helpers\Number\NumberLetter;
use App\Models\Tenant\Company;
// use Modules\Document\Services\DocumentXmlService;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Catalogs\LegendType;


class LegendInput
{
    public static function set($inputs)
    {
        $legends = [];
        if(array_key_exists('legends', $inputs)) {
            if($inputs['legends']) {
                foreach ($inputs['legends'] as $row)
                {
                    $code = $row['code'];
                    $value = $row['value'];

                    $legends[] = [
                        'code' => $code,
                        'value' => $value
                    ];
                }
            }
        }
        
        // if(Company::active()->operation_amazonia && in_array($inputs['document_type_id'], ['01', '03'])){

        //     $legends[] = [
        //         'code' => 2002,
        //         'value' => 'SERVICIOS PRESTADOS EN LA AMAZONÍA  REGIÓN SELVA PARA SER CONSUMIDOS EN LA MISMA'
        //     ];

        //     $legends[] = [
        //         'code' => 2001,
        //         'value' => 'BIENES TRANSFERIDOS EN LA AMAZONÍA REGIÓN SELVA PARA SER CONSUMIDOS EN LA MISMA'
        //     ];

        // }

        self::setLegendsForest($legends, $inputs);


        if(array_key_exists('total', $inputs)) {
            $legends[] = [
                'code' => 1000,
                'value' => NumberLetter::convertToLetter($inputs['total'])
            ];
        }

        return $legends;
    }

    
    /**
     *
     * Agregar leyendas region selva, amazonia
     * Solo agrega la leyenda si TODOS los productos son exonerados (no gravados)
     *
     * @param  array $legends
     * @param  array $inputs
     * @return void
     */
    public static function setLegendsForest(&$legends, $inputs)
    {

        if(Configuration::isEnabledLegendForestToXml() && in_array($inputs['document_type_id'], ['01', '03']))
        {
            // Validar que TODOS los items sean exonerados
            // Si hay al menos un producto gravado, NO agregar la leyenda
            $all_items_are_exonerated = self::checkAllItemsAreExonerated($inputs);

            if($all_items_are_exonerated)
            {
                $search_legends = LegendType::filterLegendsForest()->get();

                foreach ($search_legends as $value)
                {
                    $legends[] = [
                        'code' => $value->id,
                        'value' => $value->description
                    ];
                }
            }
        }
    }

    /**
     *
     * Verifica que todos los items del documento sean exonerados
     * Tipos de afectación exonerada: 20, 21, 30, 31, 32, 33, 34, 35, 36, 37
     *
     * @param  array $inputs
     * @return bool
     */
    private static function checkAllItemsAreExonerated($inputs)
    {
        // Tipos de afectación IGV exonerados
        $exonerated_affectation_types = [20, 21, 30, 31, 32, 33, 34, 35, 36, 37];

        if (!isset($inputs['items']) || empty($inputs['items'])) {
            return false;
        }

        foreach ($inputs['items'] as $item) {
            $affectation_type = $item['affectation_igv_type_id'] ?? null;

            // Si el tipo de afectación no es exonerado, retornar false
            if (!in_array($affectation_type, $exonerated_affectation_types)) {
                return false;
            }
        }

        // Todos los items son exonerados
        return true;
    }

}