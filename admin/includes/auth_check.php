<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../pages/login.php');
    exit;
}

// Verificar que sea administrador (rol 1)
if ($_SESSION['usuario']['id_rol'] != 1) {
    // Redirigir según su rol
    $redirects = [
        2 => '../coordinator/dashboard.php',
        3 => '../teacher/dashboard.php'
    ];
    $redirect = $redirects[$_SESSION['usuario']['id_rol']] ?? '../index.php';
    header('Location: ' . $redirect);
    exit;
}
?>