<?php

namespace App\Models;

use Core\DB;

class Author
{
    private $db;

    public function __construct()
    {
        $this->db = new DB();
    }

    public function getAllAuthors($page, $search, $limit)
    {

        $db = $this->db->connect();

        $offset = ($page - 1) * $limit;

        $searchCondition = '';
        if (!empty($search)) {
            $searchCondition = " WHERE name LIKE '%$search%'";
        }

        $statement = "SELECT * FROM authors $searchCondition ORDER BY id DESC LIMIT $limit OFFSET $offset";
        $result = $db->query($statement);

        if ($result && $result->num_rows > 0) {
            $authors = [];
            while ($result_row = $result->fetch_assoc()) {
                $author = [
                    'id'   => $result_row['id'],
                    'name' => $result_row['name'],
                    'dob'  => $result_row['dob']
                ];
                $authors[] = $author;
            }
        }

        return $authors;
    }
    
    public function addAuthor($input)
    {
        $db = $this->db->connect();

        $name = $input['name'];
        $dob = $input['dob'];

        $statement = "INSERT INTO authors (name, dob)
    VALUES ('$name', '$dob')";

        $create = $db->query($statement);
        if ($create) {

            return $db->insert_id;
        }
        return null;
    }

    function getAuthor($id)
    {
        $db = $this->db->connect();
        $statement = "SELECT * FROM authors where id = " . $id;
        $result = $db->query($statement);
        $result_row = $result->fetch_assoc();
        return $result_row;
    }

    function updateAuthor($input, $authorId)
    {
        $db = $this->db->connect();
        $fields = $this->getParams($input);
        $statement = "UPDATE authors SET $fields WHERE id = " . $authorId;
        $update = $db->query($statement);
        return $update;
    }

    function getParams($input)
    {
        $allowedFields = ['name', 'dob'];
        $filterParams = [];
        foreach ($input as $param => $value) {
            if (in_array($param, $allowedFields)) {
                $filterParams[] = "$param='$value'";
            }
        }
        return implode(", ", $filterParams);
    }

    function deleteAuthor($id)
    {
        $db = $this->db->connect();
        $statement = "DELETE FROM authors where id = " . $id;
        return $db->query($statement);
    }

    function getAuthorBooks( $id)
    {
        $db = $this->db->connect();
        $statement = "SELECT * FROM books WHERE author_id = " . $id;

        $result = $db->query($statement);

        if ($result && $result->num_rows > 0) {
            $books = [];
            while ($result_row = $result->fetch_assoc()) {
                $book = [
                    'id' => $result_row['id'],
                    'name' => $result_row['name'],
                    'version' => $result_row['version'],
                    'release_date' => $result_row['release_date'],
                    'isbn_code' => $result_row['isbn_code'],
                    'sbn_code' => $result_row['sbn_code'],
                    'release_date' => $result_row['release_date'],
                    'shelf_position' => $result_row['shelf_position']
                ];
                $books[] = $book;
            }

            return $books;
        }

        return null;
    }
}
