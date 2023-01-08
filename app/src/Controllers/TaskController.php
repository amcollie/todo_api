<?php

namespace App\Controllers;
use App\Gateways\TaskGateway;

class TaskController
{
    public function __construct(private TaskGateway $gateway, private ?string $user_id)
    {
    }
    public function processRequest(string $method, ?string $id): void
    {
        if (is_null($id)) {
            if ($method === 'GET') {
                echo json_encode($this->gateway->getUserTasks($this->user_id));
            } else if ($method === 'POST') {
                $task = (array) json_decode(file_get_contents("php://input"));
                $task['user_id'] = $this->user_id;
                $errors = $this->getValidationErrors($task);

                if (!empty($errors)) {
                    $this->respondUnprocessableEntity($errors);
                    return;
                }

                $task_id = $this->gateway->createUserTask($task);
                $this->respondCreated($task_id);
            } else {
                $this->respondMethodNotAllowed('GET, POST');
            }
        } else {
            $task = $this->gateway->getUserTask($id, $this->user_id);
            if (empty($task)) {
                $this->respondNotFound($id);
                return;
            }
            switch ($method) {
                case 'GET':
                    echo json_encode($task);
                    break;
                case 'PATCH':
                    $task = (array) json_decode(file_get_contents("php://input"));
                    $task['user_id'] = $this->user_id;
                    $errors = $this->getValidationErrors($task, false);

                    if (!empty($errors)) {
                        $this->respondUnprocessableEntity($errors);
                        return;
                    }

                    $rows_updated = $this->gateway->updateUserTask($id, $task);
                    echo json_encode([
                        'message' => 'Task Updated',
                        'rows_updated' => $rows_updated
                    ]);
                    break;
                case 'DELETE':
                    $rows_deleted = $rows_deleted = $this->gateway->deleteUserTask($id, $this->user_id);
                    echo json_encode([
                        'message' => 'Task Deleted',
                        'rows_deleted' => $rows_deleted
                    ]);
                    break;
                default:
                    $this->respondMethodNotAllowed('GET, PATCH, DELETE');
            }
        }
    }

    private function respondUnprocessableEntity(array $errors): void
    {
        http_response_code(422);
        echo json_encode(compact('errors'));
    }

    private function respondMethodNotAllowed(string $allowedMethods): void
    {
        http_response_code(405);
        header("Allow: $allowedMethods");
    }

    private function respondNotFound(string $id): void
    {
        http_response_code(404);
        echo json_encode([
            'message' => "Task with ID $id was not found"
        ]);
    }

    private function respondCreated(string $id): void
    {
        http_response_code(201);
        echo json_encode([
            'message' => 'Task Created',
            'id' => $id
        ]);
    }

    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];

        if ($is_new && empty($data['name'])) {
            $errors[] = 'Name is required';
        }

        if (isset($data['priority'])) {
            if (!filter_var($data['priority'], FILTER_VALIDATE_INT)) {
                $errors[] = 'Priority must be a number';
            }
        }

        return $errors;
    }
}