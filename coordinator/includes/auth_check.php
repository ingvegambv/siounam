<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: ../pages/login.php');
    exit;
}

$user = $_SESSION['usuario'];

// Verificar que sea coordinador (rol 2)
if ($user['id_rol'] != 2) {
    // Si es admin, redirigir a admin
    if ($user['id_rol'] == 1) {
        header('Location: ../admin/dashboard.php');
    } else {
        // Si es maestro, redirigir a su dashboard
        header('Location: ../teacher/dashboard.php');
    }
    exit;
}

// Verificar que tenga carrera asignada
if (empty($user['id_carrera'])) {
    $_SESSION['error'] = 'No tienes una carrera asignada. Contacta al administrador.';
    header('Location: ../pages/login.php');
    exit;
}

// Configuración para la vista
define('CARRERA_ID', $user['id_carrera']);
define('CARRERA_NOMBRE', $user['nombre_carrera'] ?? 'Sin carrera');

// Obtener el nombre de la carrera si no está en sesión
if (!isset($user['nombre_carrera']) || empty($user['nombre_carrera'])) {
    require_once __DIR__ . '/../../models/Carrera.php';
    $carreraModel = new Carrera();
    $carrera = $carreraModel->getById(CARRERA_ID);
    if ($carrera) {
        $_SESSION['usuario']['nombre_carrera'] = $carrera['nombre_carrera'];
    }
}
?>