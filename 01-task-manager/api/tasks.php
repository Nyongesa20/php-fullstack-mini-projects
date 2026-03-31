<?php
//  /task-manager/api/tasks.php
//  Handles:  POST /api/tasks.php        → create task
//            GET  /api/tasks.php        → list tasks

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Task.php';

$method = $_SERVER['REQUEST_METHOD'];
$task   = new Task();

//  POST: Create a task 
if ($method === 'POST') {

    $input = getInput();

    //  Validate fields 
    $errors = [];

    $title    = isset($input['title'])    ? trim($input['title'])    : '';
    $dueDate  = isset($input['due_date']) ? trim($input['due_date']) : '';
    $priority = isset($input['priority']) ? trim($input['priority']) : '';

    if ($title === '') {
        $errors['title'] = 'Title is required.';
    } elseif (strlen($title) > 255) {
        $errors['title'] = 'Title must not exceed 255 characters.';
    }

    if ($dueDate === '') {
        $errors['due_date'] = 'Due date is required.';
    } elseif (!validateDate($dueDate)) {
        $errors['due_date'] = 'Due date must be in YYYY-MM-DD format.';
    } elseif (!isDateTodayOrLater($dueDate)) {
        $errors['due_date'] = 'Due date must be today or a future date.';
    }

    if ($priority === '') {
        $errors['priority'] = 'Priority is required.';
    } elseif (!in_array($priority, VALID_PRIORITIES, true)) {
        $errors['priority'] = 'Priority must be one of: low, medium, high.';
    }

    if (!empty($errors)) {
        error('Validation failed.', 422, $errors);
    }

    //  Attempt to create 
    $createError = null;
    $newTask = $task->create($title, $dueDate, $priority, $createError);

    if ($newTask === null) {
        error($createError, 409);   // 409 Conflict = duplicate
    }

    created($newTask, 'Task created successfully.');
}

//  GET: List tasks 
elseif ($method === 'GET') {

    $status = query('status');

    // Validate status filter if provided
    if ($status !== null && !in_array($status, VALID_STATUSES, true)) {
        error('Invalid status filter. Must be one of: pending, in_progress, done.', 422);
    }

    $tasks = $task->getAll($status);

    if (empty($tasks)) {
        respond(200, [
            'success' => true,
            'message' => 'No tasks found.',
            'data'    => [],
        ]);
    }

    respond(200, [
        'success' => true,
        'message' => 'Tasks retrieved successfully.',
        'data'    => $tasks,
    ]);
}

//  Anything else 
else {
    error('Method not allowed. Use GET or POST.', 405);
}
