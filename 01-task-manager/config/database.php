<?php
//  Database Configuration
//  Adjust these values to match your environment

define('DB_HOST',     'localhost');
define('DB_NAME',     'task_manager');
define('DB_USER',     'root');
define('DB_PASS',     '');          // Default XAMPP has no password
define('DB_CHARSET',  'utf8mb4');


//  * Returns a PDO connection. Throws a clear error if it fails.
function getDB(): PDO
{
    static $pdo = null;          // reuse the same connection within a request

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ]);
            exit;
        }
    }

    return $pdo;
}
