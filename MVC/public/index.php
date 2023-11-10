<?php 

// index.php
require_once __DIR__ . '/../vendor/autoload.php';
use App\controllers\AuthorsController;
// require_once 'app/controllers/BooksController.php';
// require_once 'app/controllers/BorrowsController.php';
// require_once 'app/controllers/UsersController.php';
// require_once 'app/controllers/AuthController.php';
use Core\DB;
use App\Controllers\AuthController;

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Define your routes here
if (preg_match('/^\/authors\/(\d+)$/', $requestUri, $matches) && $requestMethod === 'GET') {
    $authorId = $matches[1];
    (new AuthorsController())->show($authorId);
}elseif($requestUri == '/authors' && $_SERVER['REQUEST_METHOD'] == 'POST'){
    $request = array_merge($_GET, $_POST);
    (new AuthorsController())->store($request);
}elseif(preg_match("/authors\/(\d+)\/books/", $requestUri, $matches) && $requestMethod == 'GET'){
    $authorId = $matches[1];
    (new AuthorsController())->author_with_books($authorId);
}elseif(preg_match("/authors\/(\d+)/", $requestUri, $matches) && $requestMethod == 'PATCH'){
    $authorId = $matches[1];
    $request = array_merge($_GET, $_POST);
    (new AuthorsController())->update($request, $authorId);
}elseif(preg_match("/authors\/(\d+)/", $requestUri, $matches) && $requestMethod == 'DELETE'){
    $authorId = $matches[1];
    (new AuthorsController())->destroy($authorId);
}
elseif (strpos($requestUri, '/authors') === 0 && $requestMethod == 'GET') {
    (new AuthorsController())->index();
}elseif($requestMethod=='POST' && $requestUri == '/login'){
    $request = array_merge($_GET, $_POST);
    (new AuthController())->login($request);
}
// Define routes for other controllers...

// Handle 404 errors
else {
    http_response_code(404);
    echo 'Not Found Bruh';
}
