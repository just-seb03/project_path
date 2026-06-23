<?php
session_start();
require_once "../config/db.php";

header("Content-Type: application/json");

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["action"]) &&
    $_POST["action"] === "login"
) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $tablas = ["estudiantes", "directores", "encargados", "tutores"];
    $usuario_encontrado = false;

    foreach ($tablas as $tabla) {
        $stmt = $pdo->prepare(
            "SELECT * FROM $tabla WHERE correo = :email AND password = :password",
        );
        $stmt->execute(["email" => $email, "password" => $password]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            $usuario_encontrado = true;
            $_SESSION["rol"] = $tabla;
            echo json_encode(["status" => "success"]);
            exit();
        }
    }

    if (!$usuario_encontrado) {
        echo json_encode([
            "status" => "error",
            "message" => "Datos incorrectos",
        ]);
        exit();
    }
}
?>
