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

// borrows CRUD Operations

if (strpos($url, '/borrows') === 0 && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
    $search = !empty($_GET['search']) ? $_GET['search'] : '';
    $limit = !empty($_GET['limit']) ? $_GET['limit'] : 10;

    $borrows = getAllborrows($dbConn, $page, $limit, $search);
    
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
        $borrowId = addBorrow($input, $dbConn);
        if ($borrowId !== null) {
            $input['id'] = $borrowId;
            $input['link'] = "/borrows/$borrowId";
            http_response_code(201);
        } else {
            http_response_code(500);
        }

        echo json_encode([
            'isSuccess' => $borrowId !== null,
            'message'   => $borrowId !== null ? '' : 'Could not add borrow',
            'data'      => $input
        ]);
    } catch (\Throwable $th) {
        http_response_code(422);
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
    $borrowId = $matches[1];
    $borrow = getBorrow($dbConn, $borrowId);
    
    if ($borrow !== null) {
        
        echo json_encode([
            'isSuccess' => true,
            'message'   => '',
            'data'      => $borrow
        ]);
    } else {
        
        echo json_encode([
            'isSuccess' => false,
            'message'   => 'Could not find borrow',
            'data'      => null
        ]);
    }
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

    $borrowId = $matches[1];
    $update = updateborrow($input, $dbConn, $borrowId);
    $borrow = getBorrow($dbConn, $borrowId);

    if ($borrow == null) {
        http_response_code(404); // Not Found
        echo json_encode([
            'isSuccess' => false,
            'message'   => 'No borrow Found',
            'data'      => []
        ]);
        return;
    }

    echo json_encode([
        'isSuccess' => $update ? true : false,
        'message'   => $update ? '' : 'Could not update',
        'data'      => $borrow
    ]);
}
else{
    http_response_code(503);
    echo json_encode(['error' => 'Service Unavailable']);
}


function getAllborrows($db, $page, $limit, $search)
{
    $offset = ($page - 1) * $limit;

    $searchCondition = '';
    
    if (!empty($search)) {
        $searchCondition = " WHERE books.name LIKE '%$search%' OR isbn_code LIKE '%$search%' OR users.email LIKE '%$search%' ";
    }
    
    $statement = "SELECT book_borrows.id, users.email, books.name as book_name, book_borrows.borrow_date, book_borrows.return_date 
    FROM library_db.book_borrows LEFT JOIN books ON books.id = book_borrows.book_id LEFT JOIN users ON users.id = book_borrows.user_id 
    $searchCondition LIMIT $limit OFFSET $offset;";

    $result = $db->query($statement);

    $borrows = [];
    if ($result && $result->num_rows > 0) {
        while ($result_row = $result->fetch_assoc()) {
            $borrow = [
                'id' => $result_row['id'],
                'email' => $result_row['email'],
                'book_name' => $result_row['book_name'],
                'borrow_date' => $result_row['borrow_date'],
                'return_date' => $result_row['return_date']
            ];
            $borrows[] = $borrow;
        }
    }
    return $borrows;
}

function addBorrow($input, $db)
{
    try {
        //code...
        $user = $input['user'];
        $statement = "SELECT id FROM users where email = '$user'";
        $result = $db->query($statement);
        $result_row = $result->fetch_assoc();
        if(!$result_row){
            throw new Exception('User not found!');
        }
        $user = $result_row['id'];    

        $book_id = $input['book_id'];
        $borrow_date = date('Y-m-d', strtotime($input['borrow_date']));
        $return_date = null;
        if(!empty($input['return_date'])){

            $return_date = date('Y-m-d', strtotime($input['return_date']));
        }
        $statement = "INSERT INTO book_borrows (user_id, book_id, borrow_date, return_date)
                    VALUES ('$user', '$book_id', '$borrow_date', '$return_date')";

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

function getBorrow($db, $id)
{
    $statement = "SELECT book_borrows.*, users.email as user  
                  FROM book_borrows 
                  LEFT JOIN users ON users.id = book_borrows.user_id 
                  WHERE book_borrows.id = $id";
    
    $result = $db->query($statement);
    
    if ($result && $result->num_rows > 0) {
        $result_row = $result->fetch_assoc();
        $borrow = [
            'id' => $result_row['id'],
            'user' => $result_row['user'],
            'book_id' => $result_row['book_id'],
            'borrow_date' => $result_row['borrow_date'],
            'return_date' => $result_row['return_date']
        ];
        return $borrow;
    } else {
        return null;
    }
}


function updateborrow($input, $db, $borrowId)
{
    $fields = getParams($input);
    $statement = "UPDATE book_borrows SET $fields WHERE id = " . $borrowId;
    $update = $db->query($statement);
    return $update;
}

function getParams($input)
{
    $allowedFields = ['book_id', 'borrow_date', 'return_date'];
    $filterParams = [];
    foreach ($input as $param => $value) {
        if (in_array($param, $allowedFields)) {
            if($param == 'borrow_date'){
                $value = date('Y-m-d', strtotime($input['borrow_date']));
            }
            if($param == 'return_date'){
                $value = date('Y-m-d', strtotime($input['return_date']));
            }
            $filterParams[] = "$param='$value'";
        }
    }
    return implode(", ", $filterParams);
}

