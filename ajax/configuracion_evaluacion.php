<?php
// ajax/configuracion_evaluacion.php
header('Content-Type: application/json');

require_once __DIR__ . '/../models/ConfiguracionEvaluacion.php';

session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != 3) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$configModel = new ConfiguracionEvaluacion();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'guardar':
        $idAsignacion = (int)($_POST['id_asignacion'] ?? 0);
        $idParcial = (int)($_POST['id_parcial'] ?? 0);
        $numeroParciales = (int)($_POST['numero_parciales'] ?? 4);
        $totalClases = (int)($_POST['total_clases'] ?? 20);
        
        if (!$idAsignacion || !$idParcial) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            break;
        }
        
        // Guardar configuración básica
        $data = [
            'id_asignacion' => $idAsignacion,
            'id_parcial' => $idParcial,
            'numero_parciales' => $numeroParciales,
            'total_clases' => $totalClases
        ];
        
        $result = $configModel->guardarConfiguracion($data);
        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Error al guardar configuración']);
            break;
        }
        
        // Obtener ID de configuración
        $config = $configModel->getByAsignacionParcial($idAsignacion, $idParcial);
        $idConfiguracion = $config['id_configuracion'];
        
        // Procesar aspectos
        $nombres = $_POST['aspecto_nombre'] ?? [];
        $porcentajes = $_POST['aspecto_porcentaje'] ?? [];
        $eliminar = $_POST['eliminar_aspecto'] ?? [];
        
        // Eliminar aspectos marcados
        foreach ($eliminar as $idAspecto) {
            $configModel->eliminarAspecto($idAspecto);
        }
        
        // Actualizar o insertar aspectos
        for ($i = 0; $i < count($nombres); $i++) {
            if (empty($nombres[$i]) || empty($porcentajes[$i])) continue;
            
            // Verificar si es un aspecto existente o nuevo
            // (los aspectos existentes tienen un input hidden con su ID)
            // Por simplicidad, vamos a reemplazar todos
            $configModel->agregarAspecto($idConfiguracion, $nombres[$i], $porcentajes[$i]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Configuración guardada correctamente']);
        break;
        
    case 'bloquear':
        $idConfiguracion = (int)($_POST['id_configuracion'] ?? 0);
        if (!$idConfiguracion) {
            echo json_encode(['success' => false, 'message' => 'ID de configuración no proporcionado']);
            break;
        }
        $result = $configModel->bloquear($idConfiguracion);
        echo json_encode(['success' => $result]);
        break;
        
    case 'desbloquear':
        $idConfiguracion = (int)($_POST['id_configuracion'] ?? 0);
        if (!$idConfiguracion) {
            echo json_encode(['success' => false, 'message' => 'ID de configuración no proporcionado']);
            break;
        }
        $result = $configModel->desbloquear($idConfiguracion);
        echo json_encode(['success' => $result]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
        break;
}
?>