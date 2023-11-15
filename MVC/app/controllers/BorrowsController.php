<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use App\Helpers\ValidationRules as Validate;
use App\Models\BookBorrow;
use Core\Middleware\AuthMiddleware;
class BorrowsController extends BaseController{

    public $request;

    public function __construct($request){
        $authMiddleware = new AuthMiddleware();
        $authMiddleware->handle();
        $this->request = $request;
        parent::__construct(new BookBorrow(), $request);
    }

    public function getValidationRules(): array {
        
        return [
            'user_id' => function($value) {
                return Validate::integer($value, true);
            },
            'book_id' => function($value) {
                return Validate::integer($value, true);
            },
            'borrow_date' => function($value) {
                return Validate::date($value);
            },
            'return_date' => function ($value) {
                return Validate::date($value);
            },
        ];
    }
}