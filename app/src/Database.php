<?php

namespace App;

use PDO;

class Database
{
    private ?PDO $pdo = null;

    public function __construct(
        private string $host,
        private string $user,
        private string $password,
        private string $database
    )
    {
    }

    public function getDatabase(): PDO
    {
        if (is_null($this->pdo)) {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset=utf8";
            $options = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ];


            $this->pdo = new PDO($dsn, $this->user, $this->password, $options);
        }

        return $this->pdo;
    }
}