<?php

namespace App\Models;

use Core\DB;

class BookBorrow {

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
            $searchCondition = " WHERE books.name LIKE '%$search%' OR isbn_code LIKE '%$search%' OR users.email LIKE '%$search%' ";
        }
        
        $statement = "SELECT book_borrows.id, users.email, books.name as book_name, book_borrows.borrow_date, book_borrows.return_date 
        FROM library_db.book_borrows LEFT JOIN books ON books.id = book_borrows.book_id LEFT JOIN users ON users.id = book_borrows.user_id 
        $searchCondition ORDER BY book_borrows.id DESC LIMIT $limit OFFSET $offset;";
    
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

    public function addNew($input)
    {
        $db = $this->db->connect();
        try {
            //code...
            $user = $input['user'];
            $statement = "SELECT id FROM users where email = '$user'";
            $result = $db->query($statement);
            $result_row = $result->fetch_assoc();
            if(!$result_row){
                throw new \Exception('User not found!');
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
                throw new \Exception($db->error);
                return null;
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getSingle($id)
    {
        $db = $this->db->connect();
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

    public function updateSingle($input, $borrowId)
    {
        $db = $this->db->connect();
        $fields = self::getParams($input);
        $statement = "UPDATE book_borrows SET $fields WHERE id = " . $borrowId;
        $update = $db->query($statement);
        return $update;
    }

    private function getParams($input)
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

}