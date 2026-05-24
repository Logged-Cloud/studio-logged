<?php

/**
 * HTTP trigger for the Dusk browser-test harness · runs inside fish-logged-dusk.
 *
 *   GET  /health                       → {"ok":true}
 *   POST /run   header X-Dusk-Token     → runs `php artisan dusk`, returns JSON
 *               body (optional): {"filter":"SpotsTest"}
 *
 * Used as the router script for PHP's built-in server.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
header('Content-Type: application/json');

if ($path === '/health') {
    echo json_encode(['ok' => true]);
    return;
}

if ($path !== '/run' || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(404);
    echo json_encode(['error' => 'POST /run']);
    return;
}

$expected = getenv('DUSK_TOKEN') ?: '';
$given = $_SERVER['HTTP_X_DUSK_TOKEN'] ?? '';
if ($expected === '' || ! hash_equals($expected, $given)) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    return;
}

// One run at a time.
$lock = fopen('/tmp/dusk-run.lock', 'c');
if (! $lock || ! flock($lock, LOCK_EX | LOCK_NB)) {
    http_response_code(409);
    echo json_encode(['error' => 'a Dusk run is already in progress']);
    return;
}

$body = json_decode(file_get_contents('php://input') ?: '', true);
$filter = is_array($body) ? ($body['filter'] ?? '') : '';

$cmd = 'cd /var/www/html && php artisan dusk';
if (is_string($filter) && $filter !== '' && preg_match('/^[A-Za-z0-9_:\\\\]+$/', $filter)) {
    $cmd .= ' --filter=' . escapeshellarg($filter);
}
$cmd .= ' 2>&1';

$output = [];
$exit = 0;
exec($cmd, $output, $exit);

flock($lock, LOCK_UN);
fclose($lock);

$shots = array_map('basename', glob('/var/www/html/tests/Browser/screenshots/*.png') ?: []);

echo json_encode([
    'passed'      => $exit === 0,
    'exit_code'   => $exit,
    'filter'      => $filter ?: null,
    'output'      => implode("\n", $output),
    'screenshots' => $shots,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
