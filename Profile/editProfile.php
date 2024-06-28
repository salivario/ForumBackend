<?php

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PATCH, POST, OPTIONS");

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

// Проверка на метод PATCH и наличие данных name и avatar
if ($_SERVER["REQUEST_METHOD"] === "PATCH" && isset($data['name']) && isset($data['avatar'])) {
    $name = $data['name'];
    $avatar = $data['avatar'];

    // Поиск пользователя по имени
    $query = "SELECT * FROM users WHERE name = ?";
    $stmt = $mysqli->prepare($query);
    
    // Проверка на ошибки подготовки запроса
    if ($stmt === false) {
        die(json_encode(array("message" => "Ошибка подготовки запроса: " . $mysqli->error)));
    }

    // Связывание параметров и выполнение запроса
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Если пользователь найден, обновляем его данные
        $user = $result->fetch_assoc();
        $userId = $user['id']; // Получаем ID пользователя

        // Обновление данных пользователя
        $updateQuery = "UPDATE users SET avatar = ? WHERE id = ?";
        $stmtUpdate = $mysqli->prepare($updateQuery);
        
        // Проверка на ошибки подготовки запроса
        if ($stmtUpdate === false) {
            die(json_encode(array("message" => "Ошибка подготовки запроса на обновление: " . $mysqli->error)));
        }

        $stmtUpdate->bind_param("si", $avatar, $userId);
        if ($stmtUpdate->execute()) {
            echo json_encode(array("message" => "Профиль обновлен успешно"));
        } else {
            echo json_encode(array("message" => "Ошибка обновления профиля: " . $stmtUpdate->error));
        }
    } else {
        echo json_encode(array("message" => "Пользователь не найден"));
    }
} else {
    echo json_encode(array("message" => "Данные должны быть отправлены методом PATCH и содержать name и avatar."));
}

$mysqli->close();

?>
