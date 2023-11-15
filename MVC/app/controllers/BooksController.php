<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use App\Helpers\ValidationRules as Validate;
use App\Models\Book;
use Core\Middleware\AuthMiddleware;
class BooksController extends BaseController{

    public $request;

    public function __construct($request){
        $authMiddleware = new AuthMiddleware();
        $authMiddleware->handle();
        $this->request = $request;
        parent::__construct(new Book(), $request);
    }

    public function getValidationRules(): array {
        
        return [
            'name' => function ($value) {
                return Validate::string($value, true);
            },
            'version'   => function($value) {
                return Validate::string($value, true);
            },
            'author_id' => function($value) {
                return Validate::integer($value, true);
            },
            'isbn_code' => function($value) {
                return Validate::string($value, true);
            },
            'sbn_code' => function($value) {
                return Validate::string($value);
            },
            'shelf_position' => function($value) {
                return Validate::string($value, true);
            },
            'release_date' => function ($value) {
                return Validate::date($value, true);
            },
        ];
    }
}