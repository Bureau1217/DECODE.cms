<?php

/**
 * The config file is optional. It accepts a return array with config options
 * Note: Never include more than one return statement, all options go within this single return array
 * In this example, we set debugging to true, so that errors are displayed onscreen.
 * This setting must be set to false in production.
 * All config options: https://getkirby.com/docs/reference/system/options
 */

use Kirby\Cms\Page;

return [
    'debug' => true,
    'routes' => [
        [
            'pattern' => '/',
            'action'  => function () {
                go('/panel');
            }
        ],
    ],
    'panel' => [
        'css' => '_custom-panel/main.css',
    ],
    'kql' => [
        'auth' => true, // TEMP: local test only, revert after check
    ],
    // `api.basicAuth`/`api.allowInsecure` (needed for the KQL endpoint's
    // Basic Auth — DECODE.webapp's kqlUser/kqlPassword) deliberately
    // don't live here: this file is shared with production (there's no
    // separate prod config), and allowing Basic Auth over plain HTTP is a
    // real weakening, not something to ship everywhere. See
    // config.localhost.php, which Kirby only loads when the server's
    // hostname is literally "localhost".
];
