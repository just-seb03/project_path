<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

function redirigirConMensaje(string $ruta, string $mensaje, string $tipo = 'success'): void {
    $_SESSION['flash'] = ['message' => $mensaje, 'type' => $tipo];
    header('Location: ' . $ruta);
    exit();
}

function esRolValido(string $rol): bool {
    return in_array($rol, ['estudiantes', 'encargados'], true);
}

$uploadDir = __DIR__ . '/../public/uploads/documentos';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigirConMensaje('../public/login.php?error=1', 'Solicitud no válida.', 'error');
}

if (empty($_SESSION['correo']) || empty($_SESSION['rol']) || !esRolValido($_SESSION['rol'])) {
    redirigirConMensaje('../public/login.php?error=1', 'Debes iniciar sesión para continuar.', 'error');
}

$action = $_POST['action'] ?? '';

if ($action === 'subir_informe') {
    if ($_SESSION['rol'] !== 'estudiantes') {
        redirigirConMensaje('../views/dashboard_estudiante.php', 'Solo los estudiantes pueden subir documentos.', 'error');
    }

    $idPractica = filter_input(INPUT_POST, 'id_practica', FILTER_VALIDATE_INT);
    $tipoInforme = trim((string) ($_POST['tipo_informe'] ?? 'avance'));
    $comentario = trim((string) ($_POST['comentario'] ?? ''));

    if (!$idPractica || $idPractica <= 0) {
        redirigirConMensaje('../views/dashboard_estudiante.php', 'No hay una práctica activa asociada a tu cuenta.', 'error');
    }

    if (!isset($_FILES['archivo_informe']) || $_FILES['archivo_informe']['error'] !== UPLOAD_ERR_OK) {
        redirigirConMensaje('../views/dashboard_estudiante.php', 'No se recibió ningún archivo válido.', 'error');
    }

    $nombreOriginal = basename((string) $_FILES['archivo_informe']['name']);
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    $extPermitidas = ['pdf', 'doc', 'docx'];
    if (!in_array($extension, $extPermitidas, true)) {
        redirigirConMensaje('../views/dashboard_estudiante.php', 'Solo se aceptan archivos PDF, DOC o DOCX.', 'error');
    }

    if ($_FILES['archivo_informe']['size'] > 15 * 1024 * 1024) {
        redirigirConMensaje('../views/dashboard_estudiante.php', 'El archivo supera el tamaño máximo de 15MB.', 'error');
    }

    $nombreSeguro = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombreOriginal);
    $rutaDestino = $uploadDir . '/' . $nombreSeguro;

    if (!move_uploaded_file($_FILES['archivo_informe']['tmp_name'], $rutaDestino)) {
        redirigirConMensaje('../views/dashboard_estudiante.php', 'No se pudo guardar el archivo.', 'error');
    }

    $tipoArchivo = match ($extension) {
        'pdf' => 'PDF',
        'doc', 'docx' => 'Word',
        default => 'Archivo'
    };

    $rutaRelativa = 'public/uploads/documentos/' . $nombreSeguro;

    $stmt = $pdo->prepare(
        'INSERT INTO documentos (id_practica, titulo, tipo, tipo_informe, nombre_archivo, ruta_archivo, comentario, estado, fecha_subida)
         VALUES (:id_practica, :titulo, :tipo, :tipo_informe, :nombre_archivo, :ruta_archivo, :comentario, :estado, NOW())'
    );

    $stmt->execute([
        'id_practica' => $idPractica,
        'titulo' => $nombreOriginal,
        'tipo' => $tipoArchivo,
        'tipo_informe' => $tipoInforme,
        'nombre_archivo' => $nombreOriginal,
        'ruta_archivo' => $rutaRelativa,
        'comentario' => $comentario,
        'estado' => 'pendiente'
    ]);

    redirigirConMensaje('../views/dashboard_estudiante.php', 'Documento subido correctamente y enviado a revisión.', 'success');
}

if ($action === 'revisar_documento') {
    if ($_SESSION['rol'] !== 'encargados') {
        redirigirConMensaje('../views/dashboard_encargado.php', 'Solo el encargado puede revisar documentos.', 'error');
    }

    $idDocumento = filter_input(INPUT_POST, 'id_documentos', FILTER_VALIDATE_INT);
    $estado = isset($_POST['estado']) && in_array($_POST['estado'], ['aprobado', 'rechazado'], true)
        ? $_POST['estado']
        : 'pendiente';
    $comentario = trim((string) ($_POST['comentario_revisor'] ?? ''));

    if (!$idDocumento || $idDocumento <= 0) {
        redirigirConMensaje('../views/dashboard_encargado.php', 'No se indicó el documento a revisar.', 'error');
    }

    $stmt = $pdo->prepare('UPDATE documentos SET estado = :estado, comentario_revisor = :comentario_revisor WHERE id_documentos = :id_documentos');
    $stmt->execute([
        'estado' => $estado,
        'comentario_revisor' => $comentario,
        'id_documentos' => $idDocumento,
    ]);

    redirigirConMensaje('../views/dashboard_encargado.php', 'El documento fue actualizado correctamente.', 'success');
}

redirigirConMensaje('../public/login.php?error=1', 'Acción no válida.', 'error');
