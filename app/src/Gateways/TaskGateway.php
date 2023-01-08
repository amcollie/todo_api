<?php

namespace App\Gateways;

use App\Database;
use PDO;

class TaskGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getDatabase();
    }

    public function getUserTasks(int $user_id): array
    {
        $sql = 'SELECT * FROM tasks WHERE user_id = :user_id';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $data = [];
        while ($task = $stmt->fetch()) {
            $data[] = [
                'id' => $task['id'],
                'name' => $task['name'],
                'priority' => $task['priority'],
                'is_completed' => (bool) $task['is_completed']
            ];
        }
        $stmt->closeCursor();

        return $data;
    }

    public function getUserTask(string $id, int $user_id): array
    {
        $sql = 'SELECT * FROM tasks WHERE id = :id AND user_id = :user_id';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetch();
        if ($data) {
            $data['is_completed'] = (bool) $data['is_completed'];
        } else {
            $data = [];
        }

        return $data;
    }

    public function createUserTask(array $task): int
    {
        $sql = <<<SQL
        INSERT INTO tasks (name, priority, is_completed, user_id)
        VALUES (:name, :priority, :is_completed, :user_id);
        SQL;

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':name', $task['name'], PDO::PARAM_STR);
        if (empty($task['priority'])) {
            $task['priority'] = null;
        } 
        $stmt->bindParam(':priority', $task['priority'], PDO::PARAM_STR);
        $is_completed = $task['is_completed'] ?? false;
        $stmt->bindParam(':is_completed', $is_completed, PDO::PARAM_BOOL);
        $stmt->bindParam(':user_id', $task['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function updateUserTask(string $id, array $task): int
    {
        $sql = 'UPDATE tasks SET ';
        if (isset($task['name'])) {
            $sql .= 'name = :name, ';
        }
        if (isset($task['priority'])) {
            $sql .= 'priority = :priority, ';
        }
        if (isset($task['is_completed'])) {
            $sql .= 'is_completed = :is_completed';
        }
        $sql = rtrim($sql, ', ');
        $sql .= ' WHERE id = :id AND user_id = :user_id';
        $stmt = $this->conn->prepare($sql);
        if (isset($task['name'])) {
            $stmt->bindParam(':name', $task['name'], PDO::PARAM_STR);
        }
        if (isset($task['priority'])) {
            if (empty($task['priority'])) {
                $task['priority'] = null;
            }
            $stmt->bindParam(':priority', $task['priority'], PDO::PARAM_STR);
        }
        if (isset($task['is_completed'])) {
            $is_completed = $task['is_complete'] ?? false;
            $stmt->bindParam(':is_completed', $is_completed, PDO::PARAM_BOOL);
        }
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $task['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function deleteUserTask(string $id, int $user_id): int
    {
        $sql = 'DELETE FROM tasks WHERE id = :id AND user_id = :user_id';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }
}