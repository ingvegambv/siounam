<?php
// ajax/bd.php
header('Content-Type: application/json');
require_once '../includes/connection.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] > 2) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$db = Database::getConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        $tabla = $_POST['tabla'] ?? '';
        if (!$tabla) {
            echo json_encode(['success' => false, 'message' => 'Tabla no especificada']);
            break;
        }
        
        $result = $db->query("SELECT * FROM $tabla LIMIT 1000");
        if ($result) {
            echo json_encode([
                'success' => true,
                'data' => $result->fetch_all(MYSQLI_ASSOC)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => $db->error]);
        }
        break;
        
    case 'delete':
        $tabla = $_POST['tabla'] ?? '';
        $id = (int)($_POST['id'] ?? 0);
        
        if (!$tabla || !$id) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            break;
        }
        
        // Obtener columna ID
        $result = $db->query("SHOW KEYS FROM $tabla WHERE Key_name = 'PRIMARY'");
        $row = $result->fetch_assoc();
        $idColumn = $row['Column_name'] ?? 'id';
        
        $stmt = $db->prepare("DELETE FROM $tabla WHERE $idColumn = ?");
        $stmt->bind_param("i", $id);
        
        echo json_encode(['success' => $stmt->execute()]);
        break;
        
    case 'export':
        $tabla = $_GET['tabla'] ?? '';
        if (!$tabla) {
            die('Tabla no especificada');
        }
        
        $result = $db->query("SELECT * FROM $tabla");
        if (!$result) {
            die('Error al obtener datos');
        }
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $tabla . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Encabezados
        $fields = $result->fetch_fields();
        fputcsv($output, array_column($fields, 'name'));
        
        // Datos
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
        
    case 'import':
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Error al subir el archivo']);
            break;
        }
        
        $tabla = $_POST['tabla'] ?? '';
        $file = $_FILES['file']['tmp_name'];
        
        if (!$tabla) {
            echo json_encode(['success' => false, 'message' => 'Tabla no especificada']);
            break;
        }
        
        $handle = fopen($file, 'r');
        $headers = fgetcsv($handle);
        
        $inserted = 0;
        while ($row = fgetcsv($handle)) {
            $data = array_combine($headers, $row);
            
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            
            $stmt = $db->prepare("INSERT INTO $tabla ($columns) VALUES ($placeholders)");
            
            $types = str_repeat('s', count($data));
            $values = array_values($data);
            
            $stmt->bind_param($types, ...$values);
            
            if ($stmt->execute()) {
                $inserted++;
            }
        }
        
        fclose($handle);
        
        echo json_encode([
            'success' => true,
            'message' => "$inserted registros importados"
        ]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
}