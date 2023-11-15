<?php

namespace App\Models;

use Core\DB;

class User {

    private $db;

    public function __construct()
    {
        $this->db = new DB();
    }

    
    public function getAll($page, $search, $limit)
    {
        $db = $this->db->connect();
        $offset = ($page - 1) * $limit;

        $searchCondition = '';
        
        if (!empty($search)) {
            $searchCondition = " WHERE name LIKE '%$search%' OR email LIKE '%$search%' ";
        }

        $statement = "SELECT id, name, email FROM users $searchCondition ORDER BY id DESC LIMIT $limit OFFSET $offset;";
        $result = $db->query($statement);

        $users = [];
        if ($result && $result->num_rows > 0) {
            while ($result_row = $result->fetch_assoc()) {
                $user = [
                    'id' => $result_row['id'],
                    'name' => $result_row['name'],
                    'email' => $result_row['email'],
                ];
                $users[] = $user;
            }
        }
        return $users;
    }

    public function getSingle($id)
    {
        $db = $this->db->connect();
        $statement = "SELECT id, name, email FROM users where id = " . $id;
        $result = $db->query($statement);
        $result_row = $result->fetch_assoc();
        return $result_row;
    }

    public function getSingleByEmail($email){
        $db = $this->db->connect();
        $statement = "SELECT id, name, email, password FROM users WHERE email =  '$email'";
        $result = $db->query($statement);
        $result_row = $result->fetch_assoc();
        return $result_row;
    }

    
}