<?php
//  Task Model  (OOP data layer)
//  All database queries for tasks live here.

require_once __DIR__ . '/../config/database.php';

class Task
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    //  CREATE 

       public function create(string $title, string $dueDate, string $priority, ?string &$error = null): ?array
    {
        // Check uniqueness: same title on the same due_date is forbidden
        if ($this->titleExistsOnDate($title, $dueDate)) {
            $error = "A task titled \"{$title}\" already exists for {$dueDate}.";
            return null;
        }

        $sql = 'INSERT INTO tasks (title, due_date, priority, status)
                VALUES (:title, :due_date, :priority, "pending")';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':title'    => $title,
            ':due_date' => $dueDate,
            ':priority' => $priority,
        ]);

        return $this->findById((int) $this->db->lastInsertId());
    }

    //  READ 

    public function getAll(?string $status = null): array
    {
        // FIELD() gives us custom sort order for the enum
        $sql = "SELECT * FROM tasks";

        $params = [];
        if ($status !== null) {
            $sql     .= " WHERE status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY FIELD(priority, 'high', 'medium', 'low'), due_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find a single task by its primary key. Returns null if not found.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tasks WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    //  UPDATE STATUS 

    /**
     * Advance the task to $newStatus.
     * Returns the updated task on success, or sets $error and returns null.
     */
    public function updateStatus(int $id, string $newStatus, ?string &$error = null): ?array
    {
        $task = $this->findById($id);
        if (!$task) {
            $error = 'Task not found.';
            return null;
        }

        $current  = $task['status'];
        $allowed  = STATUS_NEXT[$current];

        if ($allowed === null) {
            $error = "Task is already at the final status 'done' and cannot be changed.";
            return null;
        }

        if ($newStatus !== $allowed) {
            $error = "Invalid transition. Current status is '{$current}'. "
                   . "The only allowed next status is '{$allowed}'.";
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE tasks SET status = :status, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([':status' => $newStatus, ':id' => $id]);

        return $this->findById($id);
    }

    //  DELETE 

    /**
     * Delete a task. Only 'done' tasks may be deleted.
     * Returns true on success, or sets $error and returns false.
     */
    public function delete(int $id, ?string &$error = null, int &$errorCode = 400): bool
    {
        $task = $this->findById($id);
        if (!$task) {
            $error     = 'Task not found.';
            $errorCode = 404;
            return false;
        }

        if ($task['status'] !== 'done') {
            $error     = "Only tasks with status 'done' can be deleted. "
                       . "Current status is '{$task['status']}'.";
            $errorCode = 403;
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM tasks WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return true;
    }

    //  REPORT 

    /**
     * Count tasks grouped by priority and status for a given due_date.
     */
    public function getDailyReport(string $date): array
    {
        $sql = 'SELECT priority, status, COUNT(*) AS cnt
                FROM tasks
                WHERE due_date = :date
                GROUP BY priority, status';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':date' => $date]);
        $rows = $stmt->fetchAll();

        // Build the summary skeleton with zeros
        $summary = [];
        foreach (['high', 'medium', 'low'] as $p) {
            foreach (['pending', 'in_progress', 'done'] as $s) {
                $summary[$p][$s] = 0;
            }
        }

        // Fill in actual counts
        foreach ($rows as $row) {
            $summary[$row['priority']][$row['status']] = (int) $row['cnt'];
        }

        return $summary;
    }

    // ─ Private helpers 

    private function titleExistsOnDate(string $title, string $dueDate): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM tasks WHERE title = :title AND due_date = :due_date LIMIT 1'
        );
        $stmt->execute([':title' => $title, ':due_date' => $dueDate]);
        return (bool) $stmt->fetch();
    }
}
