<?php
//  Shared Helpers
//  Included by every API endpoint

require_once __DIR__ . '/../config/database.php';

//  CORS & headers 
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

// Handle browser pre-flight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

//  Response helpers 

function respond(int $code, array $body): void
{
    http_response_code($code);
    echo json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function success(array $data = [], string $message = 'OK', int $code = 200): void
{
    respond($code, ['success' => true, 'message' => $message, 'data' => $data]);
}

function created(array $data, string $message = 'Created successfully.'): void
{
    respond(201, ['success' => true, 'message' => $message, 'data' => $data]);
}

function error(string $message, int $code = 400, array $errors = []): void
{
    $body = ['success' => false, 'message' => $message];
    if (!empty($errors)) {
        $body['errors'] = $errors;
    }
    respond($code, $body);
}

//  Input helpers 
function getInput(): array
{
    $raw = file_get_contents('php://input');
    if (empty($raw)) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Get a query-string value, trimmed and defaulting to null.
 */
function query(string $key): ?string
{
    $val = $_GET[$key] ?? null;
    return ($val !== null && $val !== '') ? trim($val) : null;
}

//  Validation helpers 

const VALID_PRIORITIES = ['low', 'medium', 'high'];
const VALID_STATUSES   = ['pending', 'in_progress', 'done'];

/**
 * Status may only move forward in this exact order.
 */
const STATUS_NEXT = [
    'pending'     => 'in_progress',
    'in_progress' => 'done',
    'done'        => null,
];

function validateDate(string $date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function isDateTodayOrLater(string $date): bool
{
    return $date >= date('Y-m-d');
}
