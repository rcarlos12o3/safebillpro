<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Límite máximo de filas por carga
    |--------------------------------------------------------------------------
    |
    | Define el número máximo de filas que se pueden procesar en una
    | carga masiva. Esto previene problemas de memoria y timeout.
    |
    | Recomendaciones:
    | - Servidor compartido/pequeño: 500 filas
    | - Servidor dedicado mediano: 1000 filas
    | - Servidor potente: 2000 filas
    |
    */
    'max_rows' => env('BULK_UPLOAD_MAX_ROWS', 500),

    /*
    |--------------------------------------------------------------------------
    | Validar stock antes de procesar
    |--------------------------------------------------------------------------
    |
    | Si está en true, valida que haya stock suficiente antes de permitir
    | crear los documentos.
    |
    */
    'validate_stock' => env('BULK_UPLOAD_VALIDATE_STOCK', true),

    /*
    |--------------------------------------------------------------------------
    | Tiempo de expiración de batches temporales (horas)
    |--------------------------------------------------------------------------
    |
    | Los batches que no se procesen en este tiempo serán eliminados
    | automáticamente para liberar espacio.
    |
    */
    'batch_expiration_hours' => env('BULK_UPLOAD_BATCH_EXPIRATION', 24),

];
