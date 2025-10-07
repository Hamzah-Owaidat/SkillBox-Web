<?php

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

return [
    'secret' => $_ENV['JWT_SECRET'] ?? 'default_secret',
    'expires_in' => $_ENV['JWT_EXPIRES_IN'] ?? 3600,
    'algo' => $_ENV['JWT_ALGO'] ?? 'HS256',
];
