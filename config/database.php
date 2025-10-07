<?php
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

return [
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'database' => $_ENV['DB_NAME'] ?? 'skillbox', // matches DB_NAME
    'username' => $_ENV['DB_USER'] ?? 'root',     // matches DB_USER
    'password' => $_ENV['DB_PASS'] ?? '',         // matches DB_PASS
    'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4'
];
