<?php
// ajax/maestros_carrera.php
header('Content-Type: application/json');

require_once __DIR__ . '/../models/MaestroCarrera.php';
require_once __DIR__ . '/../includes/connection.php';

session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$maestroCarrera = new MaestroCarrera();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Log de depuración
file_put_contents(__DIR__ . '/debug_maestros.log', 
    date('Y-m-d H:i:s') . " - Action: $action, POST: " . print_r($_POST, true) . "\n", 
    FILE_APPEND
);

switch ($action) {
    case 'list':
        $data = $maestroCarrera->getAll();
        echo json_encode($data);
        break;
        
    case 'list_by_carrera':
        $idCarrera = isset($_POST['id_carrera']) ? (int)$_POST['id_carrera'] : null;
        if (!$idCarrera) {
            echo json_encode(['success' => false, 'message' => 'Carrera no especificada']);
            break;
        }
        $data = $maestroCarrera->getByCarrera($idCarrera);
        echo json_encode($data);
        break;
        
    case 'assign':
        if ($_SESSION['usuario']['id_rol'] > 2) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            break;
        }
        
        $idUsuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : null;
        $idCarrera = isset($_POST['id_carrera']) ? (int)$_POST['id_carrera'] : null;
        
        if (!$idUsuario || !$idCarrera) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            break;
        }
        
        $result = $maestroCarrera->assign($idUsuario, $idCarrera);
        echo json_encode(['success' => $result]);
        break;
        
    case 'delete':
        if ($_SESSION['usuario']['id_rol'] > 2) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            break;
        }
        
        $idMaestroCarrera = isset($_POST['id_maestro_carrera']) ? (int)$_POST['id_maestro_carrera'] : null;
        
        if (!$idMaestroCarrera) {
            echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
            break;
        }
        
        $result = $maestroCarrera->delete($idMaestroCarrera);
        echo json_encode(['success' => $result]);
        break;
        
    case 'delete_by_usuario':
        if ($_SESSION['usuario']['id_rol'] > 2) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            break;
        }
        
        $idUsuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : null;
        $idCarrera = isset($_POST['id_carrera']) ? (int)$_POST['id_carrera'] : null;
        
        if (!$idUsuario || !$idCarrera) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            break;
        }
        
        $result = $maestroCarrera->deleteByUsuarioCarrera($idUsuario, $idCarrera);
        echo json_encode(['success' => $result]);
        break;
        
    case 'available':
        $idCarrera = isset($_POST['id_carrera']) ? (int)$_POST['id_carrera'] : null;
        if (!$idCarrera) {
            echo json_encode(['success' => false, 'message' => 'Carrera no especificada']);
            break;
        }
        $data = $maestroCarrera->getAvailableMaestros($idCarrera);
        echo json_encode($data);
        break;
        
    case 'get_carreras_by_maestro':
        $idUsuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : null;
        if (!$idUsuario) {
            echo json_encode(['success' => false, 'message' => 'Usuario no especificado']);
            break;
        }
        $data = $maestroCarrera->getCarrerasByMaestro($idUsuario);
        echo json_encode($data);
        break;

    // ===== NUEVO: BÚSQUEDA DE MAESTROS DISPONIBLES =====
    case 'search_available':
        $query = isset($_POST['query']) ? trim($_POST['query']) : '';
        $idCarrera = isset($_POST['id_carrera']) ? (int)$_POST['id_carrera'] : null;
        
        if (empty($query) || !$idCarrera) {
            echo json_encode([]);
            break;
        }
        
        // Log para depuración
        file_put_contents(__DIR__ . '/debug_maestros.log', 
            date('Y-m-d H:i:s') . " - search_available: query=$query, id_carrera=$idCarrera\n", 
            FILE_APPEND
        );
        
        // Obtener la conexión directamente
        $db = Database::getConnection();
        
        $searchTerm = "%$query%";
        
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido_paterno, u.apellido_materno, u.usuario, u.correo
                FROM usuarios_unam u
                WHERE u.id_rol = 3 
                  AND u.activo = 1
                  AND u.id_usuario NOT IN (
                      SELECT id_usuario 
                      FROM maestro_carrera 
                      WHERE id_carrera = ?
                  )
                  AND (u.nombre LIKE ? 
                       OR u.apellido_paterno LIKE ? 
                       OR u.apellido_materno LIKE ?
                       OR u.usuario LIKE ?
                       OR CONCAT(u.nombre, ' ', u.apellido_paterno) LIKE ?)
                ORDER BY u.apellido_paterno, u.nombre
                LIMIT 20";
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            $error = $db->error;
            file_put_contents(__DIR__ . '/debug_maestros.log', 
                date('Y-m-d H:i:s') . " - Error preparando: $error\n", 
                FILE_APPEND
            );
            echo json_encode([]);
            break;
        }
        
        $stmt->bind_param(
            "isssss",
            $idCarrera,
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        
        file_put_contents(__DIR__ . '/debug_maestros.log', 
            date('Y-m-d H:i:s') . " - search_available: encontrados " . count($data) . " maestros\n", 
            FILE_APPEND
        );
        
        echo json_encode($data);
        break;
        
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Acción inválida: ' . $action
        ]);
        break;
}
?>