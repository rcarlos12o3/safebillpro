<?php

return [
    'signature_note' => env('SIGNATURE_NOTE_OSE', 'FACTURALO'),
    'signature_uri' => env('SIGNATURE_URI_OSE', 'signatureFACTURALO'),
    'api_service_url' => env('API_SERVICE_URL'),
    'api_service_token' => env('API_SERVICE_TOKEN', false),
    'sunat_alternate_server' => env('SUNAT_ALTERNATE_SERVER', false),
    'app_url_base' => env('APP_URL_BASE'),
    'multi_user_enabled' => env('MULTI_USER_ENABLED', false),
    'tenant_session_lifetime_enabled' => env('TENANT_SESSION_LIFETIME_ENABLED', false),
];
