<?php

/**
 * Loaded by Kirby only when the server's hostname is exactly "localhost"
 * (merged on top of config.php) — never on any other host, deployed
 * production included. Keep anything here that would be a real security
 * weakening outside a developer's own machine.
 */

return [
    // Required for the KQL endpoint's Basic Auth (kqlUser/kqlPassword in
    // DECODE.webapp) to work at all — Kirby rejects Basic Auth
    // credentials outright unless this is explicitly true, regardless of
    // whether they're correct (kirby/src/Cms/Auth.php, currentUserFromBasicAuth).
    'api' => [
        'basicAuth' => true,
        // Kirby also refuses Basic Auth over plain HTTP unless this is
        // set — fine on http://localhost (never leaves the machine), not
        // something to allow anywhere traffic could actually be intercepted.
        'allowInsecure' => true,
    ],
];
