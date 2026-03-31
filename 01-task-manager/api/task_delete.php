<?php
//  /task-manager/api/task_delete.php
//  Only tasks with status = 'done' may be deleted.

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Task.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'DELETE') {
    error('Method not allowed. Use DELETE.', 405);
}

//  Validate ID 
$id = query('id');
if ($id === null || !ctype_digit($id) || (int)$id <= 0) {
    error('A valid task ID is required as a query parameter (?id=N).', 400);
}

//  Attempt delete 
$taskModel   = new Task();
$deleteError = null;
$errorCode   = 400;

$deleted = $taskModel->delete((int)$id, $deleteError, $errorCode);

if (!$deleted) {
    error($deleteError, $errorCode);
}

respond(200, [
    'success' => true,
    'message' => 'Task deleted successfully.',
    'data'    => [],
]);
