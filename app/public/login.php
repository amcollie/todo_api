<?php

declare(strict_types=1);

require_once(__DIR__  . "/../bootstrap.php");

use App\Database;
use App\Gateways\UserGateway;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header("Allow: POST");
    die();
}


$data = (array) json_decode(file_get_contents("php://input"));

if (
    !array_key_exists('username', $data)
    ||!array_key_exists('password', $data)
) {
    http_response_code(400);
    echo json_encode([
        'message' => 'Missing login credentials'
    ]);
}
    
$database = new Database(
    $_ENV['DB_HOST'], 
    $_ENV['DB_USER'],
    $_ENV['DB_PASSWORD'],
    $_ENV['DB_DATABASE'], 
);
$user_gateway = new UserGateway($database);
$user = $user_gateway->getByUsername($data['username']);

if ($user === false) {
    http_response_code(401);
    echo json_encode([
        'message' => 'Invalid authenication'
    ]);
    die();
}

if (!password_verify($data['password'], $user['password'])) {
    http_response_code(401);
    echo json_encode([
        'message' => 'Invalid authenication'
    ]);
    die();
}

$access_token = base64_encode(
    json_encode([
        'id' => $user['id'],
        'name' => $user['name']
    ])
);

echo json_encode(['access_token' => $access_token]);