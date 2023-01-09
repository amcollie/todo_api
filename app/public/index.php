<?php
use App\JWTCodec;

require_once(__DIR__ . '/../bootstrap.php');

use App\Auth;
use App\Controllers\TaskController;
use App\Database;
use App\Gateways\TaskGateway;
use App\Gateways\UserGateway;


$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = explode('/', $path);

$resource = $parts[2];
$id = $parts[3] ?? null;

if ($resource != 'tasks') {
    http_response_code(404);
    die();
}


$database = new Database($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']);

$user_gateway = new UserGateway($database);
$codec = new JWTCodec($_ENV['SECRET']);
$auth = new Auth($user_gateway, $codec);
if (!$auth->authenticateAccessToken()) {
    die();
}

$user_id = $auth->getUserId();

$task_gateway = new TaskGateway($database);
$controller = new TaskController($task_gateway, $user_id);
$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);