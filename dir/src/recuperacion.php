<?php
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'solicitar') {
    $email = $_POST['email'] ?? '';
    
    if (!empty($email)) {
        $codigo = sprintf("%06d", mt_rand(1, 999999));
        
        echo json_encode(['status' => 'success', 'codigo_prueba' => $codigo]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'El correo es requerido']);
    }
} 
elseif ($action === 'verificar') {
    $codigo = $_POST['codigo'] ?? '';
    $nueva_pass = $_POST['nueva_password'] ?? '';
    
    if (!empty($codigo) && !empty($nueva_pass)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos por ingresar']);
    }
}
?>