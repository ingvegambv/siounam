<?php
// ajax/calificaciones_parciales.php
header('Content-Type: application/json');

require_once __DIR__ . '/../models/CalificacionParcial.php';
require_once __DIR__ . '/../models/ConfiguracionEvaluacion.php';

session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != 3) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$califModel = new CalificacionParcial();
$configModel = new ConfiguracionEvaluacion();

// Obtener datos del cuerpo de la petición
$input = json_decode(file_get_contents('php://input'), true);
$action = $_POST['action'] ?? $input['action'] ?? '';

switch ($action) {
    case 'guardar_alumno':
        $idAsignacion = (int)($input['id_asignacion'] ?? 0);
        $idParcial = (int)($input['id_parcial'] ?? 0);
        $idAlumno = (int)($input['id_alumno'] ?? 0);
        $idCalificacion = (int)($input['id_calificacion'] ?? 0);
        $calificaciones = $input['calificaciones'] ?? [];
        $faltas = (int)($input['faltas'] ?? 0);
        
        if (!$idAsignacion || !$idParcial || !$idAlumno) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            break;
        }
        
        // Verificar si el parcial está bloqueado
        if ($califModel->isParcialBloqueado($idAsignacion, $idParcial)) {
            echo json_encode(['success' => false, 'message' => 'Este parcial está bloqueado, no se pueden modificar calificaciones']);
            break;
        }
        
        // Obtener configuración
        $config = $configModel->getByAsignacionParcial($idAsignacion, $idParcial);
        if (!$config) {
            echo json_encode(['success' => false, 'message' => 'Configuración no encontrada']);
            break;
        }
        
        // Si no existe calificación, crearla
        if (!$idCalificacion) {
            // Buscar calificación existente o crear nueva
            $query = "SELECT id_calificacion FROM calificaciones_unam 
                      WHERE id_alumno = ? AND id_asignacion = ?";
            $db = Database::getConnection();
            $stmt = $db->prepare($query);
            $stmt->bind_param("ii", $idAlumno, $idAsignacion);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result) {
                $idCalificacion = $result['id_calificacion'];
            } else {
                // Crear nueva calificación
                $query = "INSERT INTO calificaciones_unam (id_alumno, id_asignacion) VALUES (?, ?)";
                $stmt = $db->prepare($query);
                $stmt->bind_param("ii", $idAlumno, $idAsignacion);
                $stmt->execute();
                $idCalificacion = $db->insert_id;
            }
        }
        
        // Guardar cada calificación
        foreach ($calificaciones as $calif) {
            $idAspecto = (int)($calif['id_aspecto'] ?? 0);
            $valor = $calif['calificacion'];
            if ($idAspecto) {
                $califModel->guardarCalificacion($idCalificacion, $idAspecto, $valor);
            }
        }
        
        // Guardar faltas
        $califModel->guardarFaltas($idCalificacion, $idParcial, $faltas);
        
        // Calcular y actualizar promedio final
        $promedio = $califModel->calcularPromedio($idCalificacion, $config['id_configuracion']);
        if ($promedio !== null) {
            $query = "UPDATE calificaciones_unam SET promediofinal = ? WHERE id_calificacion = ?";
            $db = Database::getConnection();
            $stmt = $db->prepare($query);
            $stmt->bind_param("di", $promedio, $idCalificacion);
            $stmt->execute();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Calificaciones guardadas correctamente',
            'id_calificacion' => $idCalificacion,
            'promedio' => $promedio
        ]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
        break;
}
?>