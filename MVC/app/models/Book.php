<?php

namespace App\Models;

use Core\DB;

class Book {

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
            $searchCondition = " WHERE books.name LIKE '%$search%' OR isbn_code LIKE '%$search%' OR sbn_code LIKE '%$search%' ";
        }

        $statement = "SELECT books.id, books.name, books.version, books.release_date, authors.name AS author_name, books.isbn_code, books.sbn_code, books.shelf_position FROM library_db.books LEFT JOIN authors ON authors.id = books.author_id $searchCondition ORDER BY books.id DESC  LIMIT $limit OFFSET $offset;";
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

    public function addNew($input)
    {
        $db = $this->db->connect();
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
        $statement = "SELECT * FROM books where id = " . $id;
        $result = $db->query($statement);
        $result_row = $result->fetch_assoc();
        return $result_row;
    }

    public function updateSingle($input, $bookId)
    {
        $db = $this->db->connect();
        $fields = self::getParams($input);
        $statement = "UPDATE books SET $fields WHERE id = " . $bookId;
        $update = $db->query($statement);
        return $update;
    }

    private function getParams($input)
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

    public function deleteSingle($id)
    {
        $db = $this->db->connect();
        $statement = "DELETE FROM books where id = " . $id;
        return $db->query($statement);
    }

}