<?php

namespace Modules\PseService\Http\Gior;

/**
 * Class Endpoints.
 */
final class Endpoints
{
    // const BETA_TOKEN = 'https://beta-pse.giortechnology.com/service/api/auth/cpe/token';
    // const BETA_GENERATE = 'https://beta-pse.giortechnology.com/service/api/cpe/generar';
    // const BETA_SEND = 'https://beta-pse.giortechnology.com/service/api/cpe/enviar';
    // const BETA_QUERY = 'https://beta-pse.giortechnology.com/service/api/cpe/consultar';

    // const TOKEN = 'https://pse.giortechnology.com/service/api/auth/cpe/token';
    // const GENERATE = 'https://pse.giortechnology.com/service/api/cpe/generar';
    // const SEND = 'https://pse.giortechnology.com/service/api/cpe/enviar';
    // const QUERY = 'https://pse.giortechnology.com/service/api/cpe/consultar';

    // Nubefact - Servidor de pruebas GRE (gratuito)
    const BETA_TOKEN = 'https://gre-test.nubefact.com/v1/clientessol/{client_id}/oauth2/token';
    const BETA_GENERATE = null; // No disponible en Nubefact
    const BETA_SEND = 'https://gre-test.nubefact.com/v1/contribuyente/gem/comprobantes/{numRucEmisor}-{codCpe}-{numSerie}-{numCpe}';
    const BETA_QUERY = 'https://gre-test.nubefact.com/v1/contribuyente/gem/comprobantes/envios/{numTicket}';

    // ValidaPSE - Producción
    const TOKEN = 'https://app.validapse.com/api/auth/cpe/token';
    const GENERATE = 'https://app.validapse.com/api/cpe/generar';
    const SEND = 'https://app.validapse.com/api/cpe/enviar';
    const QUERY = 'https://app.validapse.com/api/cpe/consultar';

    // Credenciales fijas para pruebas Nubefact
    const NUBEFACT_TEST_CLIENT_ID = 'test-85e5b0ae-255c-4891-a595-0b98c65c9854';
    const NUBEFACT_TEST_CLIENT_SECRET = 'test-Hty/M6QshYvPgItX2P0+Kw==';
    const NUBEFACT_TEST_USERNAME_SUFFIX = 'MODDATOS';
    const NUBEFACT_TEST_PASSWORD = 'MODDATOS';


}
