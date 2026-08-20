<?php
// teacher/dashboard.php
// Redirige directamente a Mis Materias
require_once __DIR__ . '/includes/auth_check.php';

// Redirigir a Mis Materias
header('Location: pages/mis_materias.php');
exit;
?>