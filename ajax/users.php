<?php
// ajax/users.php
header('Content-Type: application/json');

// Habilitar error reporting para depuración
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en pantalla, solo en log

require_once '../models/User.php';

session_start();

$user = new User();
$action = $_POST['action'] ?? '';

// Log de depuración
$debug = [
    'action' => $action,
    'post' => $_POST,
    'session' => isset($_SESSION['usuario']) ? 'Activa' : 'No activa'
];

// Registrar en archivo de log
file_put_contents(__DIR__ . '/debug.log', 
    date('Y-m-d H:i:s') . ' - ' . print_r($debug, true) . "\n", 
    FILE_APPEND
);

switch ($action) {
    
    case 'checkSession':
        if (isset($_SESSION['usuario'])) {
            echo json_encode([
                'success' => true,
                'user' => $_SESSION['usuario']
            ]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
    
    case 'list':
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            break;
        }
        echo json_encode($user->getAll());
        break;
    
    case 'get':
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            break;
        }
        $id = (int)$_POST['id'];
        $result = $user->getById($id);
        if ($result) {
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        }
        break;
    
    case 'create':
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] > 2) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            break;
        }
        
        // Validar datos
        if (empty($_POST['id_rol']) || empty($_POST['nombre']) || empty($_POST['apellido_paterno']) || 
            empty($_POST['apellido_materno']) || empty($_POST['usuario']) || empty($_POST['contrasena'])) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben estar completos']);
            break;
        }
        
        // Si es coordinador, validar que tenga carrera
        if ($_POST['id_rol'] == 2 && empty($_POST['id_carrera'])) {
            echo json_encode(['success' => false, 'message' => 'Los coordinadores deben tener una carrera asignada']);
            break;
        }
        
        try {
            // Si es maestro, procesar carreras
            if ($_POST['id_rol'] == 3 && isset($_POST['maestro_carreras'])) {
                $_POST['maestro_carreras'] = is_array($_POST['maestro_carreras']) ? 
                    $_POST['maestro_carreras'] : 
                    explode(',', $_POST['maestro_carreras']);
            }
            
            $result = $user->create($_POST);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'update':
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] > 2) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            break;
        }
        
        $id = (int)$_POST['id'];
        
        // Si es coordinador, validar que tenga carrera
        if ($_POST['id_rol'] == 2 && empty($_POST['id_carrera'])) {
            echo json_encode(['success' => false, 'message' => 'Los coordinadores deben tener una carrera asignada']);
            break;
        }
        
        try {
            // Si es maestro, procesar carreras
            if ($_POST['id_rol'] == 3 && isset($_POST['maestro_carreras'])) {
                $_POST['maestro_carreras'] = is_array($_POST['maestro_carreras']) ? 
                    $_POST['maestro_carreras'] : 
                    explode(',', $_POST['maestro_carreras']);
            }
            
            $result = $user->update($id, $_POST);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'delete':
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] > 2) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            break;
        }
        $id = (int)$_POST['id'];
        echo json_encode(['success' => $user->delete($id)]);
        break;
    
    case 'login':
        $usuario = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($usuario) || empty($password)) {
            echo json_encode([
                'success' => false,
                'message' => 'Debe ingresar usuario y contraseña.'
            ]);
            exit;
        }
        
        $login = $user->login($usuario, $password);
        
        if ($login) {
            $_SESSION['usuario'] = $login;
            echo json_encode([
                'success' => true,
                'rol' => $login['id_rol'],
                'user' => $login
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos.'
            ]);
        }
        break;

    case 'get_by_carrera':
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            break;
        }
        $idCarrera = isset($_POST['id_carrera']) ? (int)$_POST['id_carrera'] : null;
        $rol = isset($_POST['rol']) ? (int)$_POST['rol'] : null;
        $result = $user->getByCarrera($idCarrera, $rol);
        echo json_encode($result);
        break;

    case 'get_maestros_by_carrera':
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            break;
        }
        $idCarrera = isset($_POST['id_carrera']) ? (int)$_POST['id_carrera'] : null;
        if (!$idCarrera) {
            echo json_encode(['success' => false, 'message' => 'Carrera no especificada']);
            break;
        }
        $result = $user->getMaestrosByCarrera($idCarrera);
        echo json_encode($result);
        break;
    
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Acción inválida.'
        ]);
        break;
}
?>