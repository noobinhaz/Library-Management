<?php
namespace App\Controllers;
use App\Models\Author;
use App\Middleware\Cors;
use Core\Middleware\AuthMiddleware;

// AuthorsController.php

class AuthorsController {

    private $model;

    public function __construct()
    {
        $this->model = new Author();
        header('Content-Type: application/json');
        $authMiddleware = new AuthMiddleware();
        $authMiddleware->handle();
    }
    
    public function index()
    {
        $page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = !empty($_GET['search']) ? $_GET['search'] : '';
        $limit = !empty($_GET['limit']) ? $_GET['limit'] : 10;
        $authors = $this->model->getAllAuthors($page, $search, $limit);
        // echo password_hash("password", PASSWORD_DEFAULT);
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode([
            'isSuccess' => $authors !== null,
            'message'   => $authors !== null ? '' : 'Not Authors Found',
            'data'      => ['authors' => $authors ]
        ]);
    }

    public function show($id)
    {
        $author = $this->model->getAuthor($id);
        
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode([
            'isSuccess' => $author !== null,
            'message'   => $author !== null ? '' : 'Not Authors Found',
            'data'      => $author 
        ]);
    }

    public function author_with_books($id){

    }

    public function store($request){
        
        try {
            self::validateRequest($request);

            $authorId = $this->model->addAuthor($request);
            
            if ($authorId !== null) {
                
                $input['link'] = "/authors/$authorId";
                $request = array_merge($request, ['id' => $authorId, 'link' => "/authors/$authorId"]);
                http_response_code(201);
            } else {
                http_response_code(500);
                throw new \Exception('Could not create author');
            }
    
            echo json_encode([
                'isSuccess' => true,
                'message'   => 'Author Created Successfully',
                'data'      => $request 
            ]);
        } catch (\Throwable $th) {
            echo json_encode([
                'isSuccess' => false,
                'message'   => $th->getMessage(),
                'data'      => [] 
            ]);
        }

    }

    private function validateRequest($request){
        isset($request['name']) ? $request['name'] : throw new \Exception('Must provide a name for Author');
        isset($request['dob']) ? $request['dob'] : throw new \Exception('Must provide Date of Birth for Author');

        return null;
    }

    public function update($request, $id){

    }

    public function destroy($id){
        $deleteStatus = $this->model->deleteAuthor($id);

        echo json_encode([
            'isSuccess' => $deleteStatus,
            'message'   => 'Deleted ' . ($deleteStatus ? 'Success' : 'Failed'),
            'data'      => ['id' => $id]
        ]);
    }
}
