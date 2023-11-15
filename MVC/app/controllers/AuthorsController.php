<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use App\Helpers\ValidationRules as Validate;
use App\Models\Author;
use Core\Middleware\AuthMiddleware;
class AuthorsController extends BaseController{

    public $request;

    public function __construct($request){
        $authMiddleware = new AuthMiddleware();
        $authMiddleware->handle();
        $this->request = $request;
        parent::__construct(new Author(), $request);
    }

    public function getValidationRules(): array {
        return [
            'name' => function ($value) {
                return Validate::string($value, true);
            },
            'dob' => function ($value) {
                return Validate::date($value, true);
            },
        ];
    }

    public function author_with_books($id){

    }

}