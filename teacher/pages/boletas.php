<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../models/Asignacion.php';
require_once __DIR__ . '/../../models/Alumno.php';
require_once __DIR__ . '/../../models/CalificacionParcial.php';

$idAsignacion = isset($_GET['id_asignacion']) ? (int)$_GET['id_asignacion'] : 0;
$idAlumno = isset($_GET['id_alumno']) ? (int)$_GET['id_alumno'] : null;

$asignacionModel = new Asignacion();
$alumnoModel = new Alumno();
$califParcialModel = new CalificacionParcial();

// Verificar que la asignación pertenece al maestro
$asignacion = $asignacionModel->getById($idAsignacion);
if (!$idAsignacion || !$asignacion || $asignacion['id_usuario'] != MAESTRO_ID) {
    header('Location: mis_materias.php');
    exit;
}

// Obtener alumnos de la materia
$alumnos = $alumnoModel->getByGrupo($asignacion['id_grupounam']);

// Datos del alumno seleccionado
$alumnoData = null;
$boleta = [];

if ($idAlumno) {
    $alumnoData = $alumnoModel->getById($idAlumno);
    if ($alumnoData) {
        $boleta = $califParcialModel->getBoletaAlumno($idAlumno, MAESTRO_ID);
        // Filtrar solo esta materia
        $boleta = array_filter($boleta, function($item) use ($idAsignacion) {
            return $item['id_asignacion'] == $idAsignacion;
        });
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boletas - Maestro - SIOUNAM</title>
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/components/sidebar-component.js" defer></script>
    <script src="../../assets/components/header-component.js" defer></script>
    <style>
        .boleta-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-top: 20px;
        }
        .boleta-header {
            border-bottom: 2px solid #1a237e;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .boleta-header h2 {
            margin: 0;
            color: #1a237e;
        }
        .boleta-header p {
            margin: 5px 0 0;
            color: #666;
        }
        .boleta-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
        }
        .boleta-info-item {
            display: flex;
            flex-direction: column;
        }
        .boleta-info-item label {
            font-size: 0.75rem;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .boleta-info-item span {
            font-size: 1rem;
            color: #1a237e;
            font-weight: 500;
        }
        .boleta-info-item .badge-matricula {
            background: #e3f2fd;
            color: #1565c0;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        .tabla-boleta {
            width: 100%;
            border-collapse: collapse;
        }
        .tabla-boleta th {
            background: #1a237e;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 13px;
        }
        .tabla-boleta td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        .tabla-boleta tr:hover {
            background: #f5f5f5;
        }
        .aprobado {
            color: #2e7d32;
            font-weight: bold;
        }
        .reprobado {
            color: #c62828;
            font-weight: bold;
        }
        .sin-calificar {
            color: #999;
            font-style: italic;
        }
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        .filter-section select {
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            min-width: 250px;
            flex: 1;
        }
        .filter-section select:focus {
            border-color: #1a237e;
            outline: none;
        }
        .filter-section button {
            padding: 10px 24px;
            background: #1a237e;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }
        .filter-section button:hover {
            background: #0d1b5e;
        }
        .filter-section .btn-print {
            background: #2e7d32;
        }
        .filter-section .btn-print:hover {
            background: #1b5e20;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .no-data i {
            font-size: 2rem;
            color: #ccc;
            margin-bottom: 15px;
        }
        @media print {
            .filter-section, .app-layout sidebar-component, .app-layout header-component {
                display: none !important;
            }
            .boleta-container {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
            .tabla-boleta th {
                background: #333 !important;
            }
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <sidebar-component base-path="../../"></sidebar-component>
        <div class="main-content" id="mainContent">
            <header-component title="Boletas de Alumnos" icon="file-alt">
                <div slot="actions">
                    <a href="mis_materias.php" class="btn" style="background:#1a237e;color:white;padding:8px 16px;border-radius:8px;text-decoration:none;">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </header-component>

            <div style="padding: 20px;">
                <!-- Selector de alumno -->
                <div class="filter-section">
                    <form method="GET" style="display:flex;flex-wrap:wrap;gap:15px;align-items:center;width:100%;">
                        <input type="hidden" name="id_asignacion" value="<?php echo $idAsignacion; ?>">
                        <select name="id_alumno" required>
                            <option value="">Seleccionar Alumno</option>
                            <?php foreach ($alumnos as $alumno): ?>
                                <option value="<?php echo $alumno['id_alumno']; ?>" 
                                        <?php echo ($idAlumno == $alumno['id_alumno']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($alumno['matricula'] ?? 'N/A'); ?> - 
                                    <?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido_paterno']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">
                            <i class="fas fa-search"></i> Ver Boleta
                        </button>
                        <?php if ($idAlumno): ?>
                            <button type="button" class="btn-print" onclick="window.print()">
                                <i class="fas fa-print"></i> Imprimir
                            </button>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Boleta -->
                <?php if ($idAlumno && $alumnoData): ?>
                    <div class="boleta-container">
                        <div class="boleta-header">
                            <h2><i class="fas fa-file-alt"></i> Boleta de Calificaciones</h2>
                            <p><?php echo htmlspecialchars($asignacion['nombre_materia']); ?> - <?php echo htmlspecialchars($asignacion['nombre_grupo']); ?></p>
                        </div>

                        <div class="boleta-info">
                            <div class="boleta-info-item">
                                <label>Alumno</label>
                                <span><?php echo htmlspecialchars($alumnoData['nombre'] . ' ' . $alumnoData['apellido_paterno'] . ' ' . $alumnoData['apellido_materno']); ?></span>
                            </div>
                            <div class="boleta-info-item">
                                <label>Matrícula</label>
                                <span class="badge-matricula"><?php echo htmlspecialchars($alumnoData['matricula'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="boleta-info-item">
                                <label>Grupo</label>
                                <span><?php echo htmlspecialchars($alumnoData['nombre_grupo'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="boleta-info-item">
                                <label>Materia</label>
                                <span><?php echo htmlspecialchars($asignacion['nombre_materia']); ?></span>
                            </div>
                        </div>

                        <?php if (!empty($boleta)): ?>
                            <table class="tabla-boleta">
                                <thead>
                                    <tr>
                                        <th>Parcial</th>
                                        <th>Promedio</th>
                                        <th>Faltas</th>
                                        <th>Límite de Faltas</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $promedioTotal = 0;
                                    $countParciales = 0;
                                    foreach ($boleta as $item):
                                        $promedio = $item['promedio_final'] ?? null;
                                        $estado = 'Sin calificar';
                                        $claseEstado = 'sin-calificar';
                                        
                                        if ($promedio !== null) {
                                            if ($promedio >= 6) {
                                                $estado = 'Aprobado';
                                                $claseEstado = 'aprobado';
                                            } else {
                                                $estado = 'Reprobado';
                                                $claseEstado = 'reprobado';
                                            }
                                            $promedioTotal += $promedio;
                                            $countParciales++;
                                        }
                                    ?>
                                        <tr>
                                            <td>Parcial <?php echo $item['id_parcial']; ?></td>
                                            <td><strong><?php echo $promedio !== null ? number_format($promedio, 2) : 'N/A'; ?></strong></td>
                                            <td><?php echo $item['cantidad_faltas'] ?? 0; ?></td>
                                            <td><?php echo $item['limite_faltas'] ?? 'N/A'; ?></td>
                                            <td class="<?php echo $claseEstado; ?>">
                                                <?php echo $estado; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="background: #f5f5f5; font-weight: bold;">
                                        <td colspan="4">
                                            Promedio General:
                                        </td>
                                        <td>
                                            <?php if ($countParciales > 0): ?>
                                                <span class="<?php echo ($promedioTotal / $countParciales) >= 6 ? 'aprobado' : 'reprobado'; ?>">
                                                    <?php echo number_format($promedioTotal / $countParciales, 2); ?>
                                                </span>
                                            <?php else: ?>
                                                Sin calificaciones
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        <?php else: ?>
                            <div class="no-data">
                                <i class="fas fa-info-circle"></i>
                                <p>No hay calificaciones registradas para este alumno en esta materia.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($idAlumno && !$alumnoData): ?>
                    <div class="boleta-container">
                        <div class="no-data">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>Alumno no encontrado.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="boleta-container">
                        <div class="no-data">
                            <i class="fas fa-search"></i>
                            <p>Selecciona un alumno para ver su boleta de calificaciones.</p>
                            <p style="font-size:0.9rem;color:#999;">Solo se muestran alumnos de tu materia</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Evento sidebar toggle
        document.addEventListener('sidebarToggle', function(e) {
            const mainContent = document.getElementById('mainContent');
            if (mainContent) {
                mainContent.classList.toggle('expanded', e.detail.collapsed);
            }
        });
    </script>
</body>
</html>