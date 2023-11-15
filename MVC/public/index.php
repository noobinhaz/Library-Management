<?php 

// index.php
require_once __DIR__ . '/../vendor/autoload.php';
use App\controllers\AuthorsController;
use Core\DB;
use App\Controllers\AuthController;
use App\Controllers\BooksController;
use App\Controllers\BorrowsController;

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Author Routes
if (preg_match('/^\/authors\/(\d+)$/', $requestUri, $matches) && $requestMethod === 'GET') {
    $authorId = $matches[1];
    (new AuthorsController($_GET))->show($authorId);
}elseif($requestUri == '/authors' && $_SERVER['REQUEST_METHOD'] == 'POST'){
    $request = array_merge($_GET, $_POST);
    (new AuthorsController($request))->store();
}elseif(preg_match("/authors\/(\d+)\/books/", $requestUri, $matches) && $requestMethod == 'GET'){
    $authorId = $matches[1];
    $request = $_GET;
    (new AuthorsController($request))->author_with_books($authorId);
}elseif(preg_match("/authors\/(\d+)/", $requestUri, $matches) && $requestMethod == 'PATCH'){
    $authorId = $matches[1];
    $request = array_merge($_GET, $_POST);
    $request = array_merge($request, json_decode(file_get_contents("php://input"), true));
    (new AuthorsController($request))->update($authorId);
}elseif(preg_match("/authors\/(\d+)/", $requestUri, $matches) && $requestMethod == 'DELETE'){
    $authorId = $matches[1];
    $request = $_GET;
    (new AuthorsController($request))->destroy($authorId);
}elseif (strpos($requestUri, '/authors') === 0 && $requestMethod == 'GET') {
    $request = $_GET;
    (new AuthorsController($request))->index();
}//Book routes
elseif (preg_match('/^\/books\/(\d+)$/', $requestUri, $matches) && $requestMethod === 'GET') {
    $bookId = $matches[1];  
    (new BooksController($_GET))->show($bookId);
}elseif($requestUri == '/books' && $_SERVER['REQUEST_METHOD'] == 'POST'){
    $request = array_merge($_GET, $_POST);
    (new BooksController($request))->store();
}elseif(preg_match("/books\/(\d+)/", $requestUri, $matches) && $requestMethod == 'PATCH'){
    $bookId = $matches[1];
    $request = array_merge($_GET, $_POST);
    $request = array_merge($request, json_decode(file_get_contents("php://input"), true));
    (new BooksController($request))->update($bookId);
}elseif(preg_match("/books\/(\d+)/", $requestUri, $matches) && $requestMethod == 'DELETE'){
    $bookId = $matches[1];
    $request = $_GET;
    (new BooksController($request))->destroy($bookId);
}elseif (strpos($requestUri, '/books') === 0 && $requestMethod == 'GET') {
    $request = $_GET;
    (new BooksController($request))->index();
}//Borrow Routes
elseif (preg_match('/^\/borrows\/(\d+)$/', $requestUri, $matches) && $requestMethod === 'GET') {
    $borrowId = $matches[1];  
    (new BorrowsController($_GET))->show($borrowId);
}elseif($requestUri == '/books' && $_SERVER['REQUEST_METHOD'] == 'POST'){
    $request = array_merge($_GET, $_POST);
    (new BorrowsController($request))->store();
}elseif(preg_match("/borrows\/(\d+)/", $requestUri, $matches) && $requestMethod == 'PATCH'){
    $borrowId = $matches[1];
    $request = array_merge($_GET, $_POST);
    $request = array_merge($request, json_decode(file_get_contents("php://input"), true));
    (new BorrowsController($request))->update($borrowId);
}elseif (strpos($requestUri, '/borrows') === 0 && $requestMethod == 'GET') {
    $request = $_GET;
    (new BorrowsController($request))->index();
}//User Routes
elseif($requestMethod=='POST' && $requestUri == '/login'){
    $request = array_merge($_GET, $_POST);
    (new AuthController())->login($request);
}elseif($requestMethod=='GET' && strpos($requestUri, '/users') === 0){
    $request = $_GET;
    (new AuthController())->index($request);
}

else {
    http_response_code(404);
    echo 'Not Found Bruh';
}
