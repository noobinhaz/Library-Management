<?php
namespace App\Controllers;
use App\Middleware\Cors;

// AuthorsController.php

Abstract class BaseController {

    private $model;
    public $request;
    // public abstract function setRequest($request);

    public function __construct($definedModel, $request)
    {
        $this->model = $definedModel;
        $this->request = $request;
        header('Content-Type: application/json');
    }
    
    public function index()
    {
        $page = !empty($this->request['page']) ? (int)$this->request['page'] : 1;
        $search = !empty($this->request['search']) ? $this->request['search'] : '';
        $limit = !empty($this->request['limit']) ? $this->request['limit'] : 10;
        $data = $this->model->getAll($page, $search, $limit);
        // echo password_hash("password", PASSWORD_DEFAULT);
        // header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode([
            'isSuccess' => $data !== null,
            'message'   => $data !== null ? '' : 'No Data Found',
            'data'      => ['data' => $data ]
        ]);
    }

    public function show($id)
    {
        $data = $this->model->getSingle($id);
        
        http_response_code(200);
        echo json_encode([
            'isSuccess' => $data !== null,
            'message'   => $data !== null ? '' : 'No Data Found',
            'data'      => $data 
        ]);
    }

    public function store(){
        
        try {
            self::validateRequest();

            $id = $this->model->addNew($this->request);
            
            if ($id !== null) {
                
                $this->request = array_merge($this->request, ['id' => $id]);
                http_response_code(201);
            } else {
                http_response_code(500);
                throw new \Exception('Could Not Create New Entry');
            }
    
            echo json_encode([
                'isSuccess' => true,
                'message'   => 'Information Stored Successfully',
                'data'      => $this->request 
            ]);
        } catch (\Throwable $th) {
            echo json_encode([
                'isSuccess' => false,
                'message'   => $th->getMessage(),
                'data'      => [] 
            ]);
        }

    }

    abstract public function getValidationRules(): array;

    protected function validateRequest() {
        $validationRules = $this->getValidationRules();
        $request = $this->request;
        // Your generic validation logic here
        foreach ($validationRules as $field => $rule) {
            if (!isset($request[$field]) || !$rule($request[$field])) {
                throw new \Exception("Validation failed for field: $field");
            }
        }
    }

    public function update($id){
        
        $update = $this->model->updateSingle($this->request, $id);
        $data = $this->model->getSingle($id);

        if ($data == null) {
            http_response_code(404); // Not Found
            echo json_encode([
                'isSuccess' => false,
                'message'   => 'No Data Found',
                'data'      => []
            ]);
            return;
        }

        echo json_encode([
            'isSuccess' => $update ? true : false,
            'message'   => $update ? '' : 'Could not update',
            'data'      => $data
        ]);
    }

    public function destroy($id){
        $deleteStatus = $this->model->deleteSingle($id);

        echo json_encode([
            'isSuccess' => $deleteStatus,
            'message'   => 'Deleted ' . ($deleteStatus ? 'Success' : 'Failed'),
            'data'      => ['id' => $id]
        ]);
    }
}
