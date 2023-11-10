<?php

namespace App\Controllers;

use Core\DB;
use core\Config;

class AuthController
{
    public function __construct(){
        header('Content-Type: application/json');
    }
    
    public function login($request)
    {
        $email = $request['email'];
        $password = $request['password'];

        $user = $this->getUserByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            $token = $this->generateToken($user);
            // Return the token in the API response
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

    private function getUserByEmail($email)
    {
        // Fetch user from the database by email
        $db = (new DB())->connect();
        $email = $db->real_escape_string($email);
        $result = $db->query("SELECT * FROM users WHERE email = '$email'");

        return $result->fetch_assoc();
    }

    public function generateToken($user)
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
