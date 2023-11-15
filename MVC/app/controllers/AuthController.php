<?php

namespace App\Controllers;

use Core\DB;
use core\Config;
use App\Models\User;

class AuthController
{
    private $model;

    public function __construct(){
        header('Content-Type: application/json');
        $this->model = new User();
    }

    public function index($request){
        $page = !empty($request['page']) ? (int)$request['page'] : 1;
        $search = !empty($request['search']) ? $request['search'] : '';
        $limit = !empty($request['limit']) ? $request['limit'] : 10;
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
    
    public function login($request)
    {
        $email = $request['email'];
        $password = $request['password'];

        $user = $this->model->getSingleByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            $token = self::generateToken($user);
            
            echo json_encode([ 'isSuccess' => true, 
                    'message' => '', 
                    'data' => [
                        'token' => $token,
                        'user'  => $user
                    ] 
                ]);
            exit;
        } else {
            // Return an error response
            http_response_code(401);
            echo json_encode([
                    'isSuccess' => false, 
                    'message' => 'Invalid credentials', 
                    'data' => [] 
                ]);
            exit;
        }
    }


    public function logout()
    {
        // Handle user logout
        unset($_SESSION['user_id']);
        unset($_SESSION['token']);

        // Redirect or return to the login page
        header('Location: /login');
        exit;
    }

    private function generateToken($user)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(['iss' => Config::$issuer, 'sub' => $user, 'iat' => time(), 'exp' => time() + 3600]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, Config::$secretKey, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        $token = $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;

        return $token;
    }
}
