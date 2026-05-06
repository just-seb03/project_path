<?php
session_start();
header('Content-Type: application/json');

$host = 'localhost';
$db = 'path_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error BD']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $tablas = ['Directores', 'Encargados', 'Estudiantes', 'Tutores'];
        $usuario_encontrado = false;
        $rol_encontrado = '';

        foreach ($tablas as $tabla) {
            $stmt = $pdo->prepare("SELECT * FROM $tabla WHERE correo = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['password'])) {
                $usuario_encontrado = true;
                $rol_encontrado = $tabla;
                break;
            }
        }

        if ($usuario_encontrado) {
            $_SESSION['active'] = true;
            $_SESSION['rol'] = $rol_encontrado;
            $_SESSION['correo'] = $email;
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Credenciales incorrectas']);
        }
    } 
    
    elseif ($action === 'register') {
        $rol = $_POST['rol_registro'] ?? '';
        $nombres = $_POST['nombres'] ?? '';
        $apellidos = $_POST['apellidos'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);

        try {
            if ($rol === 'director') {
                $telefono = $_POST['telefono'] ?? '';
                $cod_generado = 'DIR-' . strtoupper(substr(md5(uniqid()), 0, 6));

                $stmt = $pdo->prepare("INSERT INTO Directores (nombres, apellidos, correo, telefono, password, cod_invitacion) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nombres, $apellidos, $email, $telefono, $password, $cod_generado]);
                
                echo json_encode(['status' => 'success', 'codigo' => $cod_generado]);
            } 
            
            elseif ($rol === 'encargado') {
                $telefono = $_POST['telefono'] ?? '';
                $cod_invitacion_director = $_POST['cod_invitacion'] ?? '';

                $stmt = $pdo->prepare("SELECT id_director FROM Directores WHERE cod_invitacion = ?");
                $stmt->execute([$cod_invitacion_director]);
                if (!$stmt->fetch()) {
                    echo json_encode(['status' => 'error', 'message' => 'Código de director inválido']);
                    exit;
                }

                $cod_generado = 'ENC-' . strtoupper(substr(md5(uniqid()), 0, 6));

                $stmt = $pdo->prepare("INSERT INTO Encargados (nombres, apellidos, correo, telefono, password, cod_invitacion) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nombres, $apellidos, $email, $telefono, $password, $cod_generado]);

                echo json_encode(['status' => 'success', 'codigo' => $cod_generado]);
            } 
            
            elseif ($rol === 'estudiante') {
                $rut = $_POST['rut'] ?? '';
                $cod_invitacion_encargado = $_POST['cod_invitacion'] ?? '';

                $stmt = $pdo->prepare("SELECT id_encargado FROM Encargados WHERE cod_invitacion = ?");
                $stmt->execute([$cod_invitacion_encargado]);
                if (!$stmt->fetch()) {
                    echo json_encode(['status' => 'error', 'message' => 'Código de encargado inválido']);
                    exit;
                }

                $stmt = $pdo->prepare("INSERT INTO Estudiantes (nombres, apellidos, correo, rut, password) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nombres, $apellidos, $email, $rut, $password]);

                echo json_encode(['status' => 'success']);
            } 
            
            elseif ($rol === 'tutor') {
                $cargo = $_POST['cargo'] ?? '';
                $telefono = $_POST['telefono'] ?? '';

                $stmt = $pdo->prepare("INSERT INTO Tutores (nombres, apellidos, correo, cargo, telefono, password) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nombres, $apellidos, $email, $cargo, $telefono, $password]);

                echo json_encode(['status' => 'success']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'El correo u otro dato único ya existe']);
        }
    }
}
?>