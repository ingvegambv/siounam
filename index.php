<?php
session_start();

// Si ya tiene sesión, redirigir al dashboard correspondiente
if (isset($_SESSION['usuario'])) {
    $rol = $_SESSION['usuario']['id_rol'];
    $redirects = [
        1 => 'admin/dashboard.php',
        2 => 'coordinator/dashboard.php',
        3 => 'teacher/dashboard.php'
    ];
    header('Location: ' . ($redirects[$rol] ?? 'pages/login.php'));
    exit;
}

// Redirección directa al login
header('Location: pages/login.php');
exit;
?>