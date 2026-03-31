<?php
// =============================================================
//  /task-manager/api/task_status.php
//  Handles:  PATCH /api/task_status.php?id=N  → update status
//
//  Called with:  ?id=<task_id>
//  Body (JSON):  { "status": "in_progress" }
// =============================================================

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Task.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'PATCH') {
    error('Method not allowed. Use PATCH.', 405);
}

// --- Validate ID from query string ---
$id = query('id');
if ($id === null || !ctype_digit($id) || (int)$id <= 0) {
    error('A valid task ID is required as a query parameter (?id=N).', 400);
}

// --- Validate body ---
$input     = getInput();
$newStatus = isset($input['status']) ? trim($input['status']) : '';

if ($newStatus === '') {
    error('The "status" field is required.', 422);
}
if (!in_array($newStatus, VALID_STATUSES, true)) {
    error('Status must be one of: pending, in_progress, done.', 422);
}

// --- Attempt the transition ---
$taskModel   = new Task();
$updateError = null;
$updated     = $taskModel->updateStatus((int)$id, $newStatus, $updateError);

if ($updated === null) {
    // Distinguish "not found" from "bad transition"
    $code = str_contains($updateError, 'not found') ? 404 : 422;
    error($updateError, $code);
}

respond(200, [
    'success' => true,
    'message' => 'Task status updated successfully.',
    'data'    => $updated,
]);
