<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");

require '../../vendor/autoload.php'; // подключение автозагрузчика Composer
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$mysqli = new mysqli("localhost", "root", "root", "Tavern&Shelter");

if ($mysqli->connect_errno) {
    die("Ошибка соединения: " . $mysqli->connect_error);
}

$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

if ($_SERVER["REQUEST_METHOD"] === "POST" && $data !== null) {
    $email = isset($data["email"]) ? $mysqli->real_escape_string($data["email"]) : null;
    $password = isset($data["password"]) ? $mysqli->real_escape_string($data["password"]) : null;

    if (!empty($email) && !empty($password)) {
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = $mysqli->query($query);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user["password"])) {
                $key = "adolfhitler1488";
                $payload = array(
                    "id" => $user["id"],
                    "name" => $user["name"],
                    "email" => $user["email"],
                    "avatar" => $user["avatar"],
                    "countOfMessages" => $user["count of messages"],
                    "countOfTreads" => $user["count of treads"]
                );
                $jwt = JWT::encode($payload, $key, 'HS256');
                
                echo json_encode(array(
                    "answer" => "успех!",
                    "token" => $jwt,
                    "id" => $user["id"],
                    "name" => $user["name"],
                    "email" => $user["email"],
                    "avatar" => $user["avatar"],
                    "countOfMessages" => $user["count of messages"],
                    "countOfTreads" => $user["count of treads"]
                ));
            } else {
                echo json_encode(array("message" => "Неверный пароль"));
            }
        } else {
            echo json_encode(array("message" => "Пользователь с таким email не найден"));
        }
    } else {
        echo json_encode(array("message" => "Не все данные были переданы.", "data"=>$data));
    }
} else {
    echo json_encode(array("message" => "Данные должны быть отправлены методом POST."));
}

$mysqli->close();
?>