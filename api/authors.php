<?php

require_once __DIR__ . '/../core/cors_middleware.php';
$url = $_SERVER['REQUEST_URI'];

// Checking if a slash is the first character in the route; otherwise, add it
if (strpos($url, "/") !== 0) {
    $url = "/$url";
}

global $db;

$dbInstance = new DB();
$dbConn = $dbInstance->connect($db);

// Authors CRUD Operations

if ($url == '/authors' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $authors = getAllAuthors($dbConn);
    header('Content-Type: application/json');

    echo json_encode([
        'isSuccess' => true,
        'message'   => !empty($authors) ? '' : 'No Authors Available',
        'data'      => $authors
    ]);
}

if ($url == '/authors' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = $_POST;
    $authorId = addAuthor($input, $dbConn);

    if ($authorId !== null) {
        $input['id'] = $authorId;
        $input['link'] = "/authors/$authorId";
        http_response_code(201);
    } else {
        http_response_code(500);
    }

    echo json_encode([
        'isSuccess' => $authorId !== null,
        'message'   => $authorId !== null ? '' : 'Could not add author',
        'data'      => $input
    ]);
}

if (
    preg_match("/authors\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD']
    == 'GET'
) {
    $authorId = $matches[1];
    $author = getAuthor($dbConn, $authorId);
    echo json_encode([
        'isSuccess' => !empty($author) ? true : false,
        'message'   => !empty($author) ? '' : 'Could not find author',
        'data'      => $author
    ]);
}

if (
    preg_match("/authors\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD']
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

    $authorId = $matches[1];
    $update = updateAuthor($input, $dbConn, $authorId);
    $author = getAuthor($dbConn, $authorId);

    if ($author == null) {
        http_response_code(404); // Not Found
        echo json_encode([
            'isSuccess' => false,
            'message'   => 'No Author Found',
            'data'      => []
        ]);
        return;
    }

    echo json_encode([
        'isSuccess' => $update ? true : false,
        'message'   => $update ? '' : 'Could not update',
        'data'      => $author
    ]);
}

if (
    preg_match("/authors\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD']
    == 'DELETE'
) {
    $authorId = $matches[1];
    $deleteStatus = deleteAuthor($dbConn, $authorId);
    echo json_encode([
        'isSuccess' => $deleteStatus,
        'message'   => 'Deleted ' . ($deleteStatus ? 'Success' : 'Failed'),
        'data'      => ['id' => $authorId]
    ]);
}


function getAllAuthors($db)
{
    $statement = "SELECT * FROM authors";
    $result = $db->query($statement);

    if ($result && $result->num_rows > 0) {
        $authors = [];
        while ($result_row = $result->fetch_assoc()) {
            $author = [
                'id' => $result_row['id'],
                'name' => $result_row['name']
            ];
            $authors[] = $author;
        }
    }
    return $authors;
}

function addAuthor($input, $db)
{
    $name = $input['name'];

    $statement = "INSERT INTO authors (name)
VALUES ('$name')";

    $create = $db->query($statement);
    if ($create) {

        return $db->insert_id;
    }
    return null;
}

function getauthor($db, $id)
{
    $statement = "SELECT * FROM authors where id = " . $id;
    $result = $db->query($statement);
    $result_row = $result->fetch_assoc();
    return $result_row;
}

function updateauthor($input, $db, $authorId)
{
    $fields = getParams($input);
    $statement = "UPDATE authors SET $fields WHERE id = " . $authorId;
    $update = $db->query($statement);
    return $update;
}

function getParams($input)
{
    $allowedFields = ['author'];
    $filterParams = [];
    foreach ($input as $param => $value) {
        if (in_array($param, $allowedFields)) {
            $filterParams[] = "$param='$value'";
        }
    }
    return implode(", ", $filterParams);
}

function deleteauthor($db, $id)
{
    $statement = "DELETE FROM authors where id = " . $id;
    return $db->query($statement);
}
