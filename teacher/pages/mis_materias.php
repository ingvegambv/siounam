<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../models/Asignacion.php';
require_once __DIR__ . '/../../models/Alumno.php';
require_once __DIR__ . '/../../models/ConfiguracionEvaluacion.php';

$asignacionModel = new Asignacion();
$alumnoModel = new Alumno();
$configModel = new ConfiguracionEvaluacion();

// Obtener materias asignadas al maestro
$materias = $asignacionModel->getByMaestro(MAESTRO_ID);

// Para cada materia, contar alumnos y verificar configuración
foreach ($materias as &$materia) {
    $materia['total_alumnos'] = count($alumnoModel->getByGrupo($materia['id_grupounam']));
    
    // Verificar si tiene configuración
    $config = $configModel->getByAsignacionParcial($materia['id_asignacion'], 1);
    $materia['configuracion_existe'] = $config !== null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Materias - SIOUNAM</title>
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/components/sidebar-component.js" defer></script>
    <script src="../../assets/components/header-component.js" defer></script>
    <style>
        .materias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .materia-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 4px solid #1a237e;
        }
        .materia-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 25px rgba(0,0,0,0.12);
        }
        .materia-card .materia-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .materia-card .materia-header h3 {
            margin: 0;
            color: #1a237e;
            font-size: 18px;
        }
        .materia-card .materia-header .badge-grupo {
            background: #e3f2fd;
            color: #1565c0;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .materia-card .materia-info {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .materia-card .materia-info i {
            width: 20px;
            color: #1a237e;
        }
        .materia-card .materia-info .alumnos-count {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 2px 10px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 13px;
        }
        .materia-card .materia-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            border-top: 1px solid #edf2f7;
            padding-top: 15px;
        }
        .materia-card .materia-actions .btn {
            flex: 1;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.2s;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-configurar {
            background: #1a237e;
            color: white;
        }
        .btn-configurar:hover {
            background: #0d1b5e;
        }
        .btn-evaluar {
            background: #2e7d32;
            color: white;
        }
        .btn-evaluar:hover {
            background: #1b5e20;
        }
        .btn-boletas {
            background: #1565c0;
            color: white;
        }
        .btn-boletas:hover {
            background: #0d47a1;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <sidebar-component base-path="../../"></sidebar-component>
        <div class="main-content" id="mainContent">
            <header-component title="Mis Materias" icon="chalkboard-teacher">
                <div slot="actions">
                    <span class="badge" style="background: #1a237e; color: white; padding: 8px 16px; border-radius: 20px;">
                        <i class="fas fa-user"></i> <?php echo MAESTRO_NOMBRE; ?>
                    </span>
                </div>
            </header-component>

            <div style="padding: 20px;">
                <?php if (empty($materias)): ?>
                    <div class="empty-state">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <h3>No tienes materias asignadas</h3>
                        <p style="color: #999;">Aún no te han asignado materias. Contacta al coordinador.</p>
                    </div>
                <?php else: ?>
                    <div class="materias-grid">
                        <?php foreach ($materias as $materia): ?>
                            <div class="materia-card">
                                <div class="materia-header">
                                    <h3><?php echo htmlspecialchars($materia['nombre_materia']); ?></h3>
                                    <span class="badge-grupo"><?php echo htmlspecialchars($materia['nombre_grupo']); ?></span>
                                </div>
                                <div class="materia-info">
                                    <div><i class="fas fa-university"></i> <?php echo htmlspecialchars($materia['nombre_carrera']); ?></div>
                                    <div style="margin-top: 5px;">
                                        <i class="fas fa-users"></i> 
                                        <span class="alumnos-count"><?php echo $materia['total_alumnos']; ?> alumnos</span>
                                    </div>
                                    <div style="margin-top: 5px; font-size: 13px; color: #999;">
                                        <i class="fas fa-calendar-alt"></i> Semestre: <?php echo htmlspecialchars($materia['nombre_semestre'] ?? 'N/A'); ?>
                                    </div>
                                </div>
                                <div class="materia-actions">
                                    <a href="configurar_evaluacion.php?id_asignacion=<?php echo $materia['id_asignacion']; ?>" 
                                       class="btn btn-configurar">
                                        <i class="fas fa-cog"></i> Configurar
                                    </a>
                                    <a href="evaluar.php?id_asignacion=<?php echo $materia['id_asignacion']; ?>" 
                                       class="btn btn-evaluar">
                                        <i class="fas fa-check-circle"></i> Evaluar
                                    </a>
                                    <a href="boletas.php?id_asignacion=<?php echo $materia['id_asignacion']; ?>" 
                                       class="btn btn-boletas">
                                        <i class="fas fa-file-alt"></i> Boletas
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
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