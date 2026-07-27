<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Core/Router.php';

\ = new \Router();
\->get('/audits/(?<id>[0-9]+)/cockpit', 'FakeController', 'cockpit', []);

\['REQUEST_METHOD'] = 'GET';
\ = '/audits/2/cockpit';

// Simulate Router dispatch
\ = false;
foreach (\->routes ?? [] as \) {
    // Need to use reflection to read protected routes, or just copy the dispatch logic:
}

\ = '@^/audits/(?<id>[0-9]+)/cockpit$@';
preg_match(\, \, \);
echo '<pre>';
var_dump(\);
array_shift(\);
var_dump(\);
echo '</pre>';

