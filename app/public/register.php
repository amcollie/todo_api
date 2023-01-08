<?php
use App\Database;

require_once(__DIR__ . '/../vendor/autoload.php');

use Dotenv\Dotenv;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    $database = new Database(
        $_ENV['DB_HOST'], 
        $_ENV['DB_USER'],
        $_ENV['DB_PASSWORD'],
        $_ENV['DB_DATABASE'], 
    );

    $conn = $database->getDatabase();

    $sql = <<<SQL
    INSERT INTO users (name, username, password, api_key)
    VALUES (:name, :username, :password, :api_key)
    SQL;

    $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $api_key = bin2hex(random_bytes(16));

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':name', $_POST['name'], PDO::PARAM_STR);
    $stmt->bindValue(':username', $_POST['username'], PDO::PARAM_STR);
    $stmt->bindValue(':password', $password_hash, PDO::PARAM_STR);
    $stmt->bindValue(':api_key', $api_key, PDO::PARAM_STR);

    $result = $stmt->execute();

    if ($result) {
        echo "Thank you for registering. Your API key is $api_key";
        exit();
    }

}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link 
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" 
            rel="stylesheet" 
            integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" 
            crossorigin="anonymous"
        >
        <title>Register</title>
    </head>
    <body>
        <div class="container">
            <h1>Register</h1>
            <form method="post">
                <div class="form-floating mb-3">
                    <input type="text" id="name" name="name" class="form-control" placeholder="Enter name...">
                    <label for="name">
                        Name
                    </label>
                </div>
                <div class="form-floating mb-3">
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter user name...">
                    <label for="username">
                        Username
                    </label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password..">
                    <label for="password">
                        Password
                    </label>
                </div>
                <button type="submit" class="btn btn-primary form-control">Register</button>
            </form>
        </div>
        <script 
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" 
            crossorigin="anonymous"
        >
        </script>
    </body>
</html>