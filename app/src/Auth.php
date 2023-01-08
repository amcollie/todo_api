<?php

namespace App;

use App\Gateways\UserGateway;

class Auth
{
    private int $user_id;

    public function __construct(private UserGateway $userGateway)
    {
        $this->userGateway= $userGateway;
    }

    public function authenticateApiKey(): bool
    {
        $apiKey = $this->getApiKey();

        $user = $this->userGateway->getByApiKey($apiKey);

        if ($user === false) {
            http_response_code(401);
            echo json_encode(['message' => 'invalid API key.']);
            return false;
        }
        $this->user_id = $user['id'];

        return true;
    }

    public function getUserId(): string
    {
        return $this->user_id;
    }

    private function getApiKey(): string | bool
    {
        if (empty($_SERVER['HTTP_X_API_KEY'])) {
            http_response_code(400);
            echo json_encode(['message' => 'missing API key.']);
            return false;
        }

        return $_SERVER['HTTP_X_API_KEY'];
    }
}