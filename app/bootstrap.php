<?php 

declare(strict_types=1);

require_once(__DIR__ . '/vendor/autoload.php');

set_exception_handler('\App\ErrorHandler::handleException');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

header('Content-type: application/json; charset=UTF-8');