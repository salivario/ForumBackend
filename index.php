<?php

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");

$mysqli = new mysqli("localhost", "root", "root", "Tavern&Shelter");

if ($mysqli->connect_errno) {
    die("Ошибка соединения: " . $mysqli->connect_error);
}

$request_body = file_get_contents('php://input');

$data = json_decode($request_body, true);

if ($_SERVER["REQUEST_METHOD"] === "POST" && $data !== null) {
    $name = isset($data["nickname"]) ? $data["nickname"] : null;
    $email = isset($data["email"]) ? $data["email"] : null;
    $password = isset($data["password"]) ? $data["password"] : null;


    if (!empty($name) && !empty($email) && !empty($password)) {
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";

        if ($mysqli->query($query) === TRUE) {
        } else {
            echo json_encode(array("message" => "Ошибка при создании учетной записи: " . $mysqli->error));
        }
    } else {
        echo json_encode(array("message" => "Не удалось создать учетную запись. Не все данные были переданы.", "data" => $data));
    }
} else {
    echo json_encode(array("message" => "Не удалось создать учетную запись. Данные должны быть отправлены методом POST."));
}

$mysqli->close();

?>
