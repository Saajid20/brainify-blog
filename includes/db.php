<?php
//path to env file 
$envFile = __DIR__ . '/../.env';


if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
} else {
    
    if ($_SERVER['SERVER_NAME'] === 'localhost') {
        die("Configuration error: .env file missing.");
    }
}

$host = $_ENV['DB_HOST'] ?? '';
$user = $_ENV['DB_USER'] ?? '';
$pass = $_ENV['DB_PASS'] ?? '';
$dbname = $_ENV['DB_NAME'] ?? '';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    
    die("Database connection failed.");
}
?>