<?php
// ajax/logout.php
header('Content-Type: application/json');

session_start();

// Destruir completamente la sesión
$_SESSION = array();

// Si se usa una cookie de sesión, eliminarla
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Eliminar también los datos de sessionStorage en el frontend
echo json_encode(['success' => true]);
exit;