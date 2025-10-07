<?php
namespace App\Helpers;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHelper {
    public static function generate(array $userPayload) : string {
        $key = getenv('JWT_SECRET');
        $exp = time() + (int)(getenv('JWT_EXPIRES_IN') ?: 3600);
        $payload = [
            'iss' => getenv('APP_URL') ?: 'http://localhost',
            'sub' => $userPayload['id'],
            'iat' => time(),
            'exp' => $exp,
            'data' => [
                'id' => $userPayload['id'],
                'email' => $userPayload['email'],
                'name' => $userPayload['name']
            ]
        ];
        return JWT::encode($payload, $key, 'HS256');
    }

    public static function validate(string $token) {
        try {
            $key = getenv('JWT_SECRET');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return false;
        }
    }
}
