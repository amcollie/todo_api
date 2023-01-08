<?php

declare(strict_types=1);

namespace App\Gateways;

use App\Database;
use PDO;

class UserGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getDatabase();
    }

    public function getByApiKey(string $apiKey): array | false
    {
        $sql = <<<SQL
        SELECT *
        FROM users
        WHERE api_key = :apiKey
        SQL;

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':apiKey', $apiKey);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByUsername(string $username): array |false
    {
        $sql = <<<SQL
        SELECT *
        FROM users
        WHERE username = :username
        SQL;

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}