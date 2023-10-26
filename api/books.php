<?php

require_once __DIR__ . '/../core/cors_middleware.php';
$url = $_SERVER['REQUEST_URI'];
header('Content-Type: application/json');

// Checking if a slash is the first character in the route; otherwise, add it
if (strpos($url, "/") !== 0) {
    $url = "/$url";
}

global $db;

$dbInstance = new DB();
$dbConn = $dbInstance->connect($db);

// books CRUD Operations

if (strpos($url, '/books') === 0 && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
    $search = !empty($_GET['search']) ? $_GET['search'] : '';
    $limit = !empty($_GET['limit']) ? $_GET['limit'] : 10;

    $books = getAllbooks($dbConn, $page, $search, $limit);


    echo json_encode([
        'isSuccess' => true,
        'message'   => !empty($books) ? '' : 'No books Available',
        'data'      => $books
    ]);
}

if ($url == '/books' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = $_POST;
    try {
        //code...
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
    } catch (\Throwable $th) {
        echo json_encode([
            'isSuccess' => false,
            'message'   => $th->getMessage(),
            'data'      => []
        ]);
    }
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
} else {
    http_response_code(503);
    echo json_encode(['error' => 'Service Unavailable']);
}


function getAllbooks($db, $page, $search, $limit)
{
    $offset = ($page - 1) * $limit;

    $searchCondition = '';
    
    if (!empty($search)) {
        $searchCondition = " WHERE books.name LIKE '%$search%' OR isbn_code LIKE '%$search%' OR sbn_code LIKE '%$search%' ";
    }

    $statement = "SELECT books.id, books.name, books.version, books.release_date, authors.name AS author_name, books.isbn_code, books.sbn_code, books.shelf_position FROM library_db.books LEFT JOIN authors ON authors.id = books.author_id $searchCondition LIMIT $limit OFFSET $offset;";
    $result = $db->query($statement);

    $books = [];
    if ($result && $result->num_rows > 0) {
        while ($result_row = $result->fetch_assoc()) {
            $book = [
                'id' => $result_row['id'],
                'name' => $result_row['name'],
                'version' => $result_row['version'],
                'author_name' => $result_row['author_name'],
                'release_date' => $result_row['release_date'],
                'isbn_code' => $result_row['isbn_code'],
                'sbn_code' => $result_row['sbn_code'],
                'shelf_position' => $result_row['shelf_position']
            ];
            $books[] = $book;
        }
    }
    return $books;
}

function addbook($input, $db)
{
    try {
        //code...
        $name = $input['name'];
        $version = $input['version'];
        $author_id = $input['author_id'];
        $isbn_code = $input['isbn_code'];
        $sbn_code = $input['sbn_code'];
        $release_date = date('Y-m-d', strtotime($input['release_date']));
        $shelf_position = $input['shelf_position'];

        $statement = "INSERT INTO books (name, version, author_id, isbn_code, sbn_code, release_date, shelf_position)
                    VALUES ('$name', '$version', $author_id, '$isbn_code', '$sbn_code', '$release_date', '$shelf_position')";

        $create = $db->query($statement);



        if ($create) {

            return $db->insert_id;
        } else {
            throw new Exception($db->error);
            return null;
        }
    } catch (\Throwable $th) {
        throw $th;
    }
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
    $allowedFields = ['name', 'version', 'author_id', 'isbn_code', 'sbn_code', 'release_date', 'shelf_position'];
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
