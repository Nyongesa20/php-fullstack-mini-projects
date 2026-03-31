<?php
//  /task-manager/api/report.php
//  Handles:  GET /api/report.php?date=YYYY-MM-DD

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Task.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    error('Method not allowed. Use GET.', 405);
}

//  Validate date parameter 
$date = query('date');

if ($date === null) {
    error('The "date" query parameter is required. Format: YYYY-MM-DD', 422);
}

if (!validateDate($date)) {
    error('Invalid date format. Use YYYY-MM-DD (e.g. 2026-04-01)', 422);
}

//  Build report 
$taskModel = new Task();
$summary   = $taskModel->getDailyReport($date);

respond(200, [
    'success' => true,
    'date'    => $date,
    'summary' => $summary,
]);
