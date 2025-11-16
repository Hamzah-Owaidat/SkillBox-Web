<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHelper
{
    public static function generate(array $userPayload): string
    {
        $key = $_ENV['JWT_SECRET'];
        $exp = time() + self::parseExpiry($_ENV['JWT_EXPIRES_IN'] ?? '3600');
        $alg = $_ENV['JWT_ALGO'] ?: 'HS256';
        $role = $userPayload['role'] ?? null;
        $payload = [
            'iss' => $_ENV['APP_URL'] ?: 'http://localhost',
            'sub' => $userPayload['id'],
            'iat' => time(),
            'exp' => $exp,
            'data' => [
                'id' => $userPayload['id'],
                'email' => $userPayload['email'],
                'full_name' => $userPayload['full_name'],
                'role' => $role,
            ]
        ];
        return JWT::encode($payload, $key, $alg);
    }

    public static function validate(string $token)
    {
        try {
            $key = $_ENV['JWT_SECRET'];
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return false;
        }
    }

    private static function parseExpiry(string $value): int
    {
        $value = trim(strtolower($value));

        if (preg_match('/^(\d+)([smhd])$/', $value, $matches)) {
            $num = (int)$matches[1];
            $unit = $matches[2];
            return match ($unit) {
                's' => $num,
                'm' => $num * 60,
                'h' => $num * 3600,
                'd' => $num * 86400,
                default => 3600,
            };
        }

        // fallback: assume it's seconds
        return (int)$value;
    }
}
