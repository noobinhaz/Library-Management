<?php

// AuthMiddleware.php

namespace Core\Middleware;

use Core\Config;

class AuthMiddleware
{
    public function handle()
    {
        // Check if the Authorization header is set
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        // Extract the token from the header
        $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'];
        $token = $this->extractBearerToken($authorizationHeader);

        // Validate the token (you need to implement this method)
        if (!$this->validateToken($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }

        // Token is valid, you can proceed with the request
    }

    private function extractBearerToken($authorizationHeader)
    {
        // Check if the header starts with "Bearer"
        if (strpos($authorizationHeader, 'Bearer ') === 0) {
            // Extract and return the token
            return substr($authorizationHeader, 7);
        }

        return null;
    }

    private function validateToken($token)
    {
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = explode('.', $token);

        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlHeader));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlPayload));
        $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlSignature));

        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, Config::$secretKey, true);

        // Check if the decoded signature matches the expected signature
        return hash_equals($signature, $expectedSignature);
    }
}
