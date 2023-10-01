<?php

$url = $_SERVER['REQUEST_URI'];

// Checking if a slash is the first character in the route; otherwise, add it
if (strpos($url, "/") !== 0) {
    $url = "/$url";
}

global $db;

$dbInstance = new DB();
$dbConn = $dbInstance->connect($db);

// books CRUD Operations

if ($url == '/books' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $books = getAllbooks($dbConn);
    echo json_encode([
        'isSuccess' => true,
        'message'   => !empty($books) ? '' : 'No books Available',
        'data'      => $books
    ]);
}

if ($url == '/books' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = $_POST;
    $bookId = addbook($input, $dbConn);

    if ($bookId !== null) {
        $input['id'] = $bookId;
        $input['link'] = "/books/$bookId";
        http_response_code(201);
    } else {
        http_response_code(500);
    }

    echo json_encode([
        'isSuccess' => $bookId !== null,
        'message'   => $bookId !== null ? '' : 'Could not add book',
        'data'      => $input
    ]);
}

if (
    preg_match("/books\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD']
    == 'GET'
) {
    $bookId = $matches[1];
    $book = getbook($dbConn, $bookId);
    echo json_encode([
        'isSuccess' => !empty($book) ? true : false,
        'message'   => !empty($book) ? '' : 'Could not find book',
        'data'      => $book
    ]);
}

if (
    preg_match("/books\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD']
    == 'PATCH'
) {
    $input = json_decode(file_get_contents("php://input"), true);

    if ($input === null) {
        http_response_code(400); // Bad Request
        echo json_encode([
            'isSuccess' => false,
            'message'   => 'Invalid JSON data',
            'data'      => []
        ]);
        return;
    }

    $bookId = $matches[1];
    $update = updatebook($input, $dbConn, $bookId);
    $book = getbook($dbConn, $bookId);

    if ($book == null) {
        http_response_code(404); // Not Found
        echo json_encode([
            'isSuccess' => false,
            'message'   => 'No book Found',
            'data'      => []
        ]);
        return;
    }

    echo json_encode([
        'isSuccess' => $update ? true : false,
        'message'   => $update ? '' : 'Could not update',
        'data'      => $book
    ]);
}

if (
    preg_match("/books\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD']
    == 'DELETE'
) {
    $bookId = $matches[1];
    $deleteStatus = deletebook($dbConn, $bookId);
    echo json_encode([
        'isSuccess' => $deleteStatus,
        'message'   => 'Deleted ' . ($deleteStatus ? 'Success' : 'Failed'),
        'data'      => ['id' => $bookId]
    ]);
}


function getAllbooks($db)
{
    $statement = "SELECT * FROM books";
    $result = $db->query($statement);

    if ($result && $result->num_rows > 0) {
        $books = [];
        while ($result_row = $result->fetch_assoc()) {
            $book = [
                'id' => $result_row['id'],
                'name' => $result_row['name']
            ];
            $books[] = $book;
        }
    }
    return $books;
}

function addbook($input, $db)
{
    $name = $input['name'];

    $statement = "INSERT INTO books (name)
VALUES ('$name')";

    $create = $db->query($statement);
    if ($create) {

        return $db->insert_id;
    }
    return null;
}

function getbook($db, $id)
{
    $statement = "SELECT * FROM books where id = " . $id;
    $result = $db->query($statement);
    $result_row = $result->fetch_assoc();
    return $result_row;
}

function updatebook($input, $db, $bookId)
{
    $fields = getParams($input);
    $statement = "UPDATE books SET $fields WHERE id = " . $bookId;
    $update = $db->query($statement);
    return $update;
}

function getParams($input)
{
    $allowedFields = ['book'];
    $filterParams = [];
    foreach ($input as $param => $value) {
        if (in_array($param, $allowedFields)) {
            $filterParams[] = "$param='$value'";
        }
    }
    return implode(", ", $filterParams);
}

function deletebook($db, $id)
{
    $statement = "DELETE FROM books where id = " . $id;
    return $db->query($statement);
}