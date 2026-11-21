<?php

/*
|--------------------------------------------------------------------------
| Public API for the marketing website
|--------------------------------------------------------------------------
|
| Endpoints under api/public/* are called from the browser by the marketing
| site at www.e-certificate.com.my, not by the participant app. They get their
| own CORS rules, applied by App\Http\Middleware\PublicApiCors, because
| config/cors.php allows every origin on api/* and the API tab can narrow that
| list for the participant app at any time. Neither is right for these routes:
| they should answer only the marketing site, and that must not change when
| someone edits the participant app's domains.
|
| Certificate verification is the first route here. The CHIP checkout planned
| for the pricing page is meant to reuse the same paths and allow-list.
|
| The environment is read only in this file, so `php artisan config:cache`
| keeps working.
|
*/

return [

    'cors' => [

        'paths' => ['api/public/*'],

        // Exact origins, comma separated. Both the www and the bare domain are
        // listed because the bare domain is served too, and a wildcard such as
        // https://*.e-certificate.com.my does not match it. Entries containing
        // "*" or anything beyond scheme://host[:port] are ignored.
        'allowed_origins' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env(
                'PUBLIC_API_ALLOWED_ORIGINS',
                'https://www.e-certificate.com.my,https://e-certificate.com.my'
            ))
        ))),

        // The local preview server for the marketing site. The middleware adds
        // these only while APP_ENV is local, so they can never reach production.
        // The environment is checked there rather than here because it is not
        // reliably known yet while configuration files load.
        'local_origins' => ['http://127.0.0.1:4173'],

        // GET for verification and packages (simple requests, no preflight).
        // POST with a JSON body for the CHIP checkout, which the browser
        // preflights; Content-Type is allowed for that reason.
        'allowed_methods' => ['GET', 'POST'],

        'allowed_headers' => ['Accept', 'Content-Type'],

        'max_age' => 600,

    ],

];
