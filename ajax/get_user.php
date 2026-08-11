<?php
// ajax/get_user.php
header('Content-Type: application/json');
session_start();

if (isset($_SESSION['usuario'])) {
    echo json_encode($_SESSION['usuario']);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
}