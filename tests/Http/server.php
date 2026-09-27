<?php

declare(strict_types=1);

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url(is_string($requestUri) ? $requestUri : '/', PHP_URL_PATH);
if ($path === '/redirect') {
    header('Location: http://127.0.0.1:1/elsewhere', true, 302);
    return true;
}
if ($path === '/slow') {
    header('Content-Type: application/json');
    echo '{"data":';
    flush();
    sleep(5);
    echo '{}}';
    return true;
}
header('Content-Type: application/json');
echo '{"data":{"ok":true}}';
return true;
