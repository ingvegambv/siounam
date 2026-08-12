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
        
        // Validar tabla permitida
        $tablasPermitidas = ['usuarios_unam', 'alumnos_unam', 'materias', 'grupo_unam', 'carrera_unam', 'calificaciones_unam'];
        if (!in_array($tabla, $tablasPermitidas)) {
            echo json_encode(['success' => false, 'message' => 'Tabla no permitida']);
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
        
        $tablasPermitidas = ['usuarios_unam', 'alumnos_unam', 'materias', 'grupo_unam', 'carrera_unam', 'calificaciones_unam'];
        if (!in_array($tabla, $tablasPermitidas)) {
            echo json_encode(['success' => false, 'message' => 'Tabla no permitida']);
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
        
        $tablasPermitidas = ['usuarios_unam', 'alumnos_unam', 'materias', 'grupo_unam', 'carrera_unam', 'calificaciones_unam'];
        if (!in_array($tabla, $tablasPermitidas)) {
            die('Tabla no permitida');
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
        
        $tablasPermitidas = ['usuarios_unam', 'alumnos_unam', 'materias', 'grupo_unam', 'carrera_unam', 'calificaciones_unam'];
        if (!in_array($tabla, $tablasPermitidas)) {
            echo json_encode(['success' => false, 'message' => 'Tabla no permitida']);
            break;
        }
        
        // Leer el archivo CSV
        $handle = fopen($file, 'r');
        if (!$handle) {
            echo json_encode(['success' => false, 'message' => 'Error al abrir el archivo']);
            break;
        }
        
        // Leer encabezados
        $headers = fgetcsv($handle);
        if (!$headers) {
            echo json_encode(['success' => false, 'message' => 'El archivo CSV no tiene encabezados válidos']);
            fclose($handle);
            break;
        }
        
        // Limpiar encabezados (remover BOM si existe)
        $headers = array_map(function($h) {
            return trim($h, "\xEF\xBB\xBF");
        }, $headers);
        
        // Iniciar transacción
        $db->begin_transaction();
        
        try {
            $inserted = 0;
            $errors = [];
            $rowNumber = 1;
            
            // Si la tabla es usuarios_unam, necesitamos encriptar contraseñas
            $esUsuarios = ($tabla === 'usuarios_unam');
            
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                
                // Verificar que la fila tenga el mismo número de columnas
                if (count($row) !== count($headers)) {
                    $errors[] = "Fila $rowNumber: Número de columnas no coincide";
                    continue;
                }
                
                // Combinar encabezados con valores
                $data = array_combine($headers, $row);
                
                // Si es usuarios_unam, encriptar la contraseña
                if ($esUsuarios && isset($data['contrasena']) && !empty($data['contrasena'])) {
                    $data['contrasena'] = password_hash($data['contrasena'], PASSWORD_DEFAULT);
                }
                
                // Si es usuarios_unam y id_carrera está vacío, establecer NULL
                if ($esUsuarios && isset($data['id_carrera']) && empty($data['id_carrera'])) {
                    $data['id_carrera'] = null;
                }
                
                // Construir la consulta
                $columns = array_keys($data);
                $placeholders = array_fill(0, count($data), '?');
                
                $sql = "INSERT INTO $tabla (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $db->prepare($sql);
                
                if (!$stmt) {
                    $errors[] = "Fila $rowNumber: Error en la preparación de la consulta - " . $db->error;
                    continue;
                }
                
                // Crear los tipos y valores para bind_param
                $types = '';
                $values = [];
                foreach ($data as $key => $value) {
                    // Determinar el tipo
                    if ($value === null || $value === '') {
                        $types .= 's';
                        $values[] = null;
                    } elseif (is_numeric($value) && strpos($value, '.') === false) {
                        $types .= 'i';
                        $values[] = (int)$value;
                    } elseif (is_numeric($value)) {
                        $types .= 'd';
                        $values[] = (float)$value;
                    } else {
                        $types .= 's';
                        $values[] = $value;
                    }
                }
                
                // Bind parameters
                $stmt->bind_param($types, ...$values);
                
                if ($stmt->execute()) {
                    $inserted++;
                } else {
                    $errors[] = "Fila $rowNumber: " . $stmt->error;
                }
                
                $stmt->close();
            }
            
            fclose($handle);
            
            // Si hay errores, pero algunos se insertaron, preguntar si continuar
            if (!empty($errors)) {
                $db->rollback();
                echo json_encode([
                    'success' => false,
                    'message' => "Se encontraron errores en el archivo. Se han revertido todos los cambios.",
                    'errors' => $errors,
                    'inserted' => 0,
                    'total_rows' => $rowNumber - 1
                ]);
            } else {
                $db->commit();
                echo json_encode([
                    'success' => true,
                    'message' => "$inserted registros importados correctamente",
                    'inserted' => $inserted,
                    'total_rows' => $rowNumber - 1
                ]);
            }
            
        } catch (Exception $e) {
            $db->rollback();
            fclose($handle);
            echo json_encode([
                'success' => false,
                'message' => 'Error al importar: ' . $e->getMessage()
            ]);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
}