<?php
session_start();
header("Content-Type: application/json");

try {
    require_once __DIR__ . "/../config/db.php";
} catch (Throwable $e) {
    echo json_encode([
        "status" => "error",
        "message" => "No se pudo conectar a la base de datos. Revisa la configuración local de MySQL.",
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    if ($_POST["action"] === "login") {
        $email = trim($_POST["email"]);
        $password = $_POST["password"];

        $tablas = ["estudiantes", "directores", "encargados", "tutores"];
        $usuario_encontrado = false;

        foreach ($tablas as $tabla) {
            $stmt = $pdo->prepare(
                "SELECT * FROM $tabla WHERE correo = :email AND password = :password",
            );
            $stmt->execute(["email" => $email, "password" => $password]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                $usuario_encontrado = true;
                $_SESSION["rol"] = $tabla;
                $_SESSION["correo"] = $usuario["correo"];

                $redirect = $tabla === "estudiantes"
                    ? "../views/dashboard_estudiante.php"
                    : "success.php";

                echo json_encode([
                    "status" => "success",
                    "message" => "Login exitoso",
                    "redirect" => $redirect,
                ]);
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

    if ($_POST["action"] === "register") {
        $rol = $_POST["rol_registro"];
        $nombres = trim($_POST["nombres"]);
        $apellidos = trim($_POST["apellidos"]);
        $correo = trim($_POST["email"]);
        $password = $_POST["password"];

        $rut = isset($_POST["rut"]) ? $_POST["rut"] : null;
        $telefono = isset($_POST["telefono"]) ? $_POST["telefono"] : null;
        $cargo = isset($_POST["cargo"]) ? $_POST["cargo"] : null;
        $id_carrera = isset($_POST["id_carrera"]) ? $_POST["id_carrera"] : null;
        $id_empresa = isset($_POST["id_empresa"]) ? $_POST["id_empresa"] : null;

        $tablas = ["estudiantes", "directores", "encargados", "tutores"];
        foreach ($tablas as $tabla) {
            $checkStmt = $pdo->prepare(
                "SELECT correo FROM $tabla WHERE correo = :correo",
            );
            $checkStmt->execute(["correo" => $correo]);
            if ($checkStmt->rowCount() > 0) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El correo ya está registrado en el sistema",
                ]);
                exit();
            }
        }

        try {
            switch ($rol) {
                case "estudiante":
                    if (!$rut || !$id_carrera) {
                        echo json_encode([
                            "status" => "error",
                            "message" =>
                                "RUT y Carrera son obligatorios para estudiantes",
                        ]);
                        exit();
                    }
                    $sql =
                        "INSERT INTO estudiantes (rut, apellidos, nombres, id_carrera, correo, password) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $rut,
                        $apellidos,
                        $nombres,
                        $id_carrera,
                        $correo,
                        $password,
                    ]);
                    break;

                case "director":
                    $sql =
                        "INSERT INTO directores (apellidos, nombres, correo, telefono, password) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $apellidos,
                        $nombres,
                        $correo,
                        $telefono,
                        $password,
                    ]);
                    break;

                case "encargado":
                    if (!$id_carrera) {
                        echo json_encode([
                            "status" => "error",
                            "message" => "La carrera es obligatoria",
                        ]);
                        exit();
                    }
                    $sql =
                        "INSERT INTO encargados (id_carrera, apellidos, nombres, correo, telefono, password) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $id_carrera,
                        $apellidos,
                        $nombres,
                        $correo,
                        $telefono,
                        $password,
                    ]);
                    break;

                case "tutor":
                    if (!$id_empresa) {
                        echo json_encode([
                            "status" => "error",
                            "message" =>
                                "La empresa es obligatoria para el tutor",
                        ]);
                        exit();
                    }
                    $sql =
                        "INSERT INTO tutores (id_empresa, apellidos, nombres, cargo, correo, telefono, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $id_empresa,
                        $apellidos,
                        $nombres,
                        $cargo,
                        $correo,
                        $telefono,
                        $password,
                    ]);
                    break;

                default:
                    echo json_encode([
                        "status" => "error",
                        "message" => "Rol no válido",
                    ]);
                    exit();
            }

            echo json_encode([
                "status" => "success",
                "message" => "Registro exitoso",
            ]);
            exit();
        } catch (PDOException $e) {
            echo json_encode([
                "status" => "error",
                "message" => "Error interno: " . $e->getMessage(),
            ]);
            exit();
        }
    }
}
?>
