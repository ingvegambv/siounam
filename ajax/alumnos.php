<?php
// ajax/alumnos.php
header('Content-Type: application/json');

require_once __DIR__ . '/../models/Alumno.php';

session_start();

$alumno = new Alumno();
$action = $_POST['action'] ?? '';

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar permisos (admin o coordinador)
if ($_SESSION['usuario']['id_rol'] > 2) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tiene permisos']);
    exit;
}

switch ($action) {
    
    case 'list':
        $data = $alumno->getAll();
        echo json_encode($data);
        break;
    
    case 'get':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            break;
        }
        $result = $alumno->getById($id);
        if ($result) {
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Alumno no encontrado']);
        }
        break;
    
    case 'search':
        $query = isset($_POST['query']) ? trim($_POST['query']) : '';
        $idCarrera = isset($_POST['id_carrera']) ? (int)$_POST['id_carrera'] : null;
        
        if (empty($query)) {
            echo json_encode([]);
            break;
        }
        
        $data = $alumno->search($query, $idCarrera);
        echo json_encode($data);
        break;
    
    case 'create':
        // Validar datos
        if (empty($_POST['matricula']) || empty($_POST['nombre']) || 
            empty($_POST['apellido_paterno']) || empty($_POST['apellido_materno']) || 
            empty($_POST['id_carrera']) || empty($_POST['id_grupounam'])) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
            break;
        }
        
        try {
            $result = $alumno->create($_POST);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Alumno creado correctamente']);
            } else {
                // Verificar si fue por matrícula duplicada
                if ($alumno->matriculaExists($_POST['matricula'])) {
                    echo json_encode(['success' => false, 'message' => 'La matrícula ya existe']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al crear el alumno']);
                }
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'update':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            break;
        }
        
        // Validar datos
        if (empty($_POST['matricula']) || empty($_POST['nombre']) || 
            empty($_POST['apellido_paterno']) || empty($_POST['apellido_materno']) || 
            empty($_POST['id_carrera']) || empty($_POST['id_grupounam'])) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
            break;
        }
        
        try {
            $result = $alumno->update($id, $_POST);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Alumno actualizado correctamente']);
            } else {
                if ($alumno->matriculaExists($_POST['matricula'], $id)) {
                    echo json_encode(['success' => false, 'message' => 'La matrícula ya existe']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar el alumno']);
                }
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            break;
        }
        
        try {
            $result = $alumno->delete($id);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Alumno eliminado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el alumno']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'migrar':
        $grupoOrigen = (int)($_POST['grupo_origen'] ?? 0);
        $grupoDestino = (int)($_POST['grupo_destino'] ?? 0);
        
        if (!$grupoOrigen || !$grupoDestino) {
            echo json_encode(['success' => false, 'message' => 'Grupos no especificados']);
            break;
        }
        
        if ($grupoOrigen == $grupoDestino) {
            echo json_encode(['success' => false, 'message' => 'Los grupos deben ser diferentes']);
            break;
        }
        
        try {
            $result = $alumno->migrar($grupoOrigen, $grupoDestino);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Alumnos migrados correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al migrar alumnos']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'get_by_carrera':
        $idCarrera = (int)($_POST['id_carrera'] ?? 0);
        if (!$idCarrera) {
            echo json_encode(['success' => false, 'message' => 'Carrera no especificada']);
            break;
        }
        $data = $alumno->getByCarrera($idCarrera);
        echo json_encode($data);
        break;
    
    case 'check_matricula':
        $matricula = $_POST['matricula'] ?? '';
        $excludeId = isset($_POST['exclude_id']) ? (int)$_POST['exclude_id'] : null;
        
        if (!$matricula) {
            echo json_encode(['success' => false, 'message' => 'Matrícula no proporcionada']);
            break;
        }
        
        $exists = $alumno->matriculaExists($matricula, $excludeId);
        echo json_encode(['exists' => $exists]);
        break;

    case 'get_semestre_actual':
        $idAlumno = (int)($_POST['id_alumno'] ?? 0);
        if (!$idAlumno) {
            echo json_encode(['success' => false, 'message' => 'Alumno no especificado']);
            break;
        }
        $result = $alumno->getSemestreActual($idAlumno);
        echo json_encode($result);
        break;

    case 'get_materias_by_alumno_semestre':
        $idAlumno = (int)($_POST['id_alumno'] ?? 0);
        $idSemestre = (int)($_POST['id_semestre'] ?? 0);
        if (!$idAlumno || !$idSemestre) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            break;
        }
        $result = $alumno->getMateriasByAlumnoSemestre($idAlumno, $idSemestre);
        echo json_encode($result);
        break;

    case 'get_semestres_by_alumno':
        $idAlumno = (int)($_POST['id_alumno'] ?? 0);
        if (!$idAlumno) {
            echo json_encode(['success' => false, 'message' => 'Alumno no especificado']);
            break;
        }
        $result = $alumno->getSemestresWithMaterias($idAlumno);
        echo json_encode($result);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
        break;
}
?>