<?php
session_start();
header("Content-Type: application/json");

require_once __DIR__ . "/../config/db.php";

$storageFile = __DIR__ . "/../data/mensajes.json";

if (!is_dir(__DIR__ . "/../data")) {
    mkdir(__DIR__ . "/../data", 0777, true);
}

function cargarMensajesDesdeArchivo($archivo) {
    if (!file_exists($archivo)) {
        return [];
    }

    $contenido = file_get_contents($archivo);
    if ($contenido === false || trim($contenido) === "") {
        return [];
    }

    $datos = json_decode($contenido, true);
    return is_array($datos) ? $datos : [];
}

function guardarMensajesEnArchivo($archivo, array $mensajes): void {
    file_put_contents($archivo, json_encode($mensajes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function obtenerMensajes(PDO $pdo, int $idPractica, string $destinatarioRol): array {
    $sql = "SELECT id_mensaje, id_practica, emisor_rol, emisor_correo, destinatario_rol, contenido, fecha_envio
            FROM mensajes
            WHERE id_practica = :id_practica AND destinatario_rol = :destinatario_rol
            ORDER BY fecha_envio ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        "id_practica" => $idPractica,
        "destinatario_rol" => $destinatarioRol,
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function guardarMensaje(PDO $pdo, array $datos): bool {
    $sql = "INSERT INTO mensajes (id_practica, emisor_rol, emisor_correo, destinatario_rol, contenido, fecha_envio)
            VALUES (:id_practica, :emisor_rol, :emisor_correo, :destinatario_rol, :contenido, :fecha_envio)";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        "id_practica" => $datos["id_practica"],
        "emisor_rol" => $datos["emisor_rol"],
        "emisor_correo" => $datos["emisor_correo"],
        "destinatario_rol" => $datos["destinatario_rol"],
        "contenido" => $datos["contenido"],
        "fecha_envio" => $datos["fecha_envio"],
    ]);
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $idPractica = isset($_GET["id_practica"]) ? (int) $_GET["id_practica"] : 0;
    $destinatarioRol = isset($_GET["destinatario_rol"]) ? trim($_GET["destinatario_rol"]) : "tutor";

    if ($idPractica <= 0) {
        echo json_encode(["status" => "success", "mensajes" => []]);
        exit();
    }

    try {
        $mensajes = obtenerMensajes($pdo, $idPractica, $destinatarioRol);
    } catch (PDOException $e) {
        $mensajes = cargarMensajesDesdeArchivo($storageFile);
        $mensajes = array_values(array_filter($mensajes, function ($mensaje) use ($idPractica, $destinatarioRol) {
            return ((int) ($mensaje["id_practica"] ?? 0)) === $idPractica
                && (($mensaje["destinatario_rol"] ?? "") === $destinatarioRol);
        }));
    }

    usort($mensajes, function ($a, $b) {
        return strtotime($a["fecha_envio"] ?? "now") <=> strtotime($b["fecha_envio"] ?? "now");
    });

    echo json_encode(["status" => "success", "mensajes" => $mensajes]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "enviar_mensaje") {
    if (empty($_SESSION["correo"]) || empty($_SESSION["rol"])) {
        echo json_encode(["status" => "error", "message" => "Sesión no válida"]);
        exit();
    }

    $idPractica = isset($_POST["id_practica"]) ? (int) $_POST["id_practica"] : 0;
    $destinatarioRol = isset($_POST["destinatario_rol"]) ? trim($_POST["destinatario_rol"]) : "tutor";
    $contenido = trim($_POST["contenido"] ?? "");

    if ($idPractica <= 0 || $contenido === "") {
        echo json_encode(["status" => "error", "message" => "Faltan datos del mensaje"]);
        exit();
    }

    $rol = $_SESSION["rol"];
    $emisorRol = $rol === "estudiantes" ? "estudiante" : ($rol === "encargados" ? "encargado" : "estudiante");
    $fechaEnvio = date("Y-m-d H:i:s");

    $datosMensaje = [
        "id_practica" => $idPractica,
        "emisor_rol" => $emisorRol,
        "emisor_correo" => $_SESSION["correo"],
        "destinatario_rol" => $destinatarioRol,
        "contenido" => $contenido,
        "fecha_envio" => $fechaEnvio,
    ];

    try {
        $guardado = guardarMensaje($pdo, $datosMensaje);
        if (!$guardado) {
            throw new Exception("No se pudo guardar en la base de datos");
        }
    } catch (Throwable $e) {
        $mensajes = cargarMensajesDesdeArchivo($storageFile);
        $mensajes[] = $datosMensaje;
        guardarMensajesEnArchivo($storageFile, $mensajes);
    }

    echo json_encode(["status" => "success", "message" => "Mensaje enviado"]);
    exit();
}

echo json_encode(["status" => "error", "message" => "Solicitud no válida"]);
