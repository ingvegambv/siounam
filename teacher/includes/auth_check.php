<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: ../pages/login.php');
    exit;
}

$user = $_SESSION['usuario'];

// Verificar que sea maestro (rol 3)
if ($user['id_rol'] != 3) {
    // Si es admin, redirigir a admin
    if ($user['id_rol'] == 1) {
        header('Location: ../admin/dashboard.php');
    } else {
        // Si es coordinador, redirigir a coordinador
        header('Location: ../coordinator/dashboard.php');
    }
    exit;
}

// Configuración para la vista
define('MAESTRO_ID', $user['id_usuario']);
define('MAESTRO_NOMBRE', $user['nombre'] . ' ' . $user['apellido_paterno']);
?>