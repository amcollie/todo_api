<?php

namespace App;

use App\Exceptions\InvalidSignatureException;
use App\Gateways\UserGateway;
use Exception;

class Auth
{
    private int $user_id;

    public function __construct(
        private UserGateway $userGateway,
        private JWTCodec $codec
    )
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

    public function authenticateAccessToken(): bool
    {
        if (!preg_match("/^Bearer\s+(?<token>.*)$/", $_SERVER['REDIRECT_HTTP_AUTHORIZATION'], $matches)) {
            http_response_code(400);
            echo json_encode([
                'message' => 'incomplete authorization header'
            ]);
            return false;
        }

        try {
            $data = $this->codec->jwtDecode($matches['token']);
        } catch (InvalidSignatureException) {
            http_response_code(401);
            echo json_encode([
                'message' => 'invalid signature.'
            ]);
            return false;
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'message' => '$e->getMessage()'
            ]);
            return false;
        }

        $this->user_id = $data['sub'];

        return true;
    }
}