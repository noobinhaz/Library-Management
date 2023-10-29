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

header('Content-Type: application/json');

if(strpos($url, '/users') === 0 && $_SERVER['REQUEST_METHOD'] == 'GET'){
    $search = $_GET;
    
    $users = searchUsers($dbConn, $search);
    echo json_encode([
        'isSuccess' => true,
        'message' => !empty($users) ? '' : 'No Users Found',
        'data' => $users
    ]);
}
else{
    http_response_code(503);
    echo json_encode(['error' => 'Service Unavailable']);
}

function searchUsers($db, $search)
{
    $whereClauses = [];

    if (count($search) > 0) {
        if (isset($search['name'])) {
            $name = $db->real_escape_string($search['name']);
            $whereClauses[] = "name LIKE '%$name%'";
        }
        if (isset($search['email'])) {
            $email = $db->real_escape_string($search['email']);
            $whereClauses[] = "email LIKE '%$email%'";
        }
    }

    if (count($whereClauses) > 0) {
        $whereClause = implode(' AND ', $whereClauses);
        $sql = "SELECT * FROM users WHERE $whereClause ORDER BY id DESC;";
    } else {
        $sql = "SELECT * FROM users ORDER BY id DESC;";
    }

    $result = $db->query($sql);

    $users = [];
    if ($result && $result->num_rows > 0) {
        while ($result_row = $result->fetch_assoc()) {
            $user = [
                'id' => $result_row['id'],
                'name' => $result_row['name'],
                'email' => $result_row['email'],
                'role'  => $result_row['role']
            ];
            $users[] = $user;
        }
    }

    return $users;
}
