<?php
$routes = [
    'authors'   => 'authors.php',
    'books'     => 'books.php',
    'borrows'   => 'borrows.php',
    'users'     => 'users.php',
    '/'         => 'index.php',
];

header('Content-Type: application/json');

$request_uri = $_SERVER['REQUEST_URI'];
$route = explode('/', parse_url($request_uri, PHP_URL_PATH))[1];

if (!array_key_exists($route, $routes)) {
    http_response_code(404);
    echo json_encode(['error'=> 'Not found']);
}