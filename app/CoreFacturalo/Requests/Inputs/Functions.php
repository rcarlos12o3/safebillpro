<?php

namespace App\CoreFacturalo\Requests\Inputs;

use App\Models\Tenant\Document;
use App\Models\Tenant\Series;
use Carbon\Carbon;
use Exception;
use Modules\Document\Models\SeriesConfiguration;

class Functions
{
    public static function newNumber($soap_type_id, $document_type_id, $series, $number, $model)
    {

        if ($number === '#') {

            $document = $model::select('number')
                                    ->where('document_type_id', $document_type_id)
                                    ->where('series', $series)
                                    ->orderBy('number', 'desc')
                                    ->first();

            if($document){

                return (int)$document->number+1;

            }else{

                $series_configuration = SeriesConfiguration::where([['document_type_id',$document_type_id],['series',$series]])->first();
                return ($series_configuration) ? (int) $series_configuration->number:1;

            }

        }

        return $number;

        // if ($number === '#') {
        //     $document = $model::select('number')
        //                         ->where('soap_type_id', $soap_type_id)
        //                         ->where('document_type_id', $document_type_id)
        //                         ->where('series', $series)
        //                         ->orderBy('number', 'desc')
        //                         ->first();
        //     return ($document)?(int)$document->number+1:1;
        // }
        // return $number;
    }

    public static function filename($company, $document_type_id, $series, $number)
    {
        return join('-', [$company->number, $document_type_id, $series, $number]);
    }

    public static function validateUniqueDocument($soap_type_id, $document_type_id, $series, $number, $model)
    {
        $document = $model::where('soap_type_id', $soap_type_id)
                        ->where('document_type_id', $document_type_id)
                        ->where('series', $series)
                        ->where('number', $number)
                        ->first();
        if($document) {
            throw new Exception("El documento: {$document_type_id} {$series}-{$number} ya se encuentra registrado.");
        }
    }

    public static function identifier($soap_type_id, $date_of_issue, $model)
    {
        $path = explode('\\', $model);
        $prefix = array_pop($path) === 'Voided' ? 'RA' : 'RC';
        $dateFormatted = Carbon::parse($date_of_issue)->format('Ymd');

        $last = $model::where('soap_type_id', $soap_type_id)
                      ->where('date_of_issue', $date_of_issue)
                      ->orderByRaw('CAST(SUBSTRING_INDEX(identifier, \'-\', -1) AS UNSIGNED) DESC')
                      ->value('identifier');

        if ($last) {
            $parts = explode('-', $last);
            $numeration = ((int) end($parts)) + 1;
        } else {
            $numeration = 1;
        }

        return join('-', [$prefix, $dateFormatted, $numeration]);
    }

    /**
     * @param      $inputs
     * @param      $key
     * @param null $default
     *
     * @return mixed|null
     */
    public static function valueKeyInArray($inputs, $key, $default = null)
    {
        return (isset($inputs[$key]) && null !== $inputs[$key]) ? $inputs[$key] : $default;
    }
}
