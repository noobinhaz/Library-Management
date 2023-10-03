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

// borrows CRUD Operations

if ($url == '/borrows' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $borrows = getAllborrows($dbConn);
    header('Content-Type: application/json');
    echo json_encode([
        'isSuccess' => true,
        'message'   => !empty($borrows) ? '' : 'No borrows Available',
        'data'      => $borrows
    ]);
}

if ($url == '/borrows' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = $_POST;
    try {
        //code...
        $bookId = addbook($input, $dbConn);
        if ($bookId !== null) {
            $input['id'] = $bookId;
            $input['link'] = "/borrows/$bookId";
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
    preg_match("/borrows\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD']
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
    preg_match("/borrows\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD']
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
    preg_match("/borrows\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD']
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


function getAllborrows($db)
{
    $statement = "SELECT book_borrows.id, users.email, books.name as book_name, book_borrows.borrow_date, book_borrows.return_date, 
    FROM library_db.book_borrows LEFT JOIN books ON books.id = book_borrows.book_id LEFT JOIN users ON users.id = book_borrows.user_id;";
    $result = $db->query($statement);

    $borrows = [];
    if ($result && $result->num_rows > 0) {
        while ($result_row = $result->fetch_assoc()) {
            $book = [
                'id' => $result_row['id'],
                'name' => $result_row['name'],
                'version' => $result_row['version'],
                'author_name' => $result_row['author_name'],
                'isbn_code' => $result_row['isbn_code'],
                'sbn_code' => $result_row['sbn_code'],
                'shelf_position' => $result_row['shelf_position']
            ];
            $borrows[] = $book;
        }
    }
    return $borrows;
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

        $statement = "INSERT INTO borrows (name, version, author_id, isbn_code, sbn_code, release_date, shelf_position)
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
    $statement = "SELECT * FROM borrows where id = " . $id;
    $result = $db->query($statement);
    $result_row = $result->fetch_assoc();
    return $result_row;
}

function updatebook($input, $db, $bookId)
{
    $fields = getParams($input);
    $statement = "UPDATE borrows SET $fields WHERE id = " . $bookId;
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
    $statement = "DELETE FROM borrows where id = " . $id;
    return $db->query($statement);
}
