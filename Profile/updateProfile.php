<?php

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php'; 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$mysqli = new mysqli("localhost", "root", "root", "Tavern&Shelter");

if ($mysqli->connect_errno) {
    die(json_encode(array("message" => "Ошибка соединения: " . $mysqli->connect_error)));
}

$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($data['token']) && isset($data['email'])) {
    $token = $data['token'];
    $key = "adolfhitler1488";

    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $userId = $decoded->id;
        $userEmail = $data['email'];

        $query = "SELECT * FROM users WHERE id = '$userId' AND email = '$userEmail'";
        $result = $mysqli->query($query);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            $payload = array(
                "id" => $user["id"],
                "name" => $user["name"],
                "email" => $user["email"],
                "avatar" => $user["avatar"],
                "countOfMessages" => $user["count of messages"],
                "countOfTreads" => $user["count of treads"]
            );
            $new_jwt = JWT::encode($payload, $key, 'HS256');
            
            echo json_encode(array("token" => $new_jwt));
        } else {
            echo json_encode(array("message" => "Пользователь не найден"));
        }
    } catch (Exception $e) {
        echo json_encode(array("message" => "Невалидный токен"));
    }
} else {
    echo json_encode(array("message" => "Данные должны быть отправлены методом POST и содержать token и email."));
}

$mysqli->close();

?>
