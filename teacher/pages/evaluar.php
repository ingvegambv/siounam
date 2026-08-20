<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../models/ConfiguracionEvaluacion.php';
require_once __DIR__ . '/../../models/CalificacionParcial.php';
require_once __DIR__ . '/../../models/Asignacion.php';
require_once __DIR__ . '/../../models/Alumno.php';

$idAsignacion = isset($_GET['id_asignacion']) ? (int)$_GET['id_asignacion'] : 0;
$idParcial = isset($_GET['id_parcial']) ? (int)$_GET['id_parcial'] : 1;

if (!$idAsignacion) {
    header('Location: mis_materias.php');
    exit;
}

$configModel = new ConfiguracionEvaluacion();
$califParcialModel = new CalificacionParcial();
$asignacionModel = new Asignacion();
$alumnoModel = new Alumno();

// Verificar que la asignación pertenece al maestro
$asignacion = $asignacionModel->getById($idAsignacion);
if (!$asignacion || $asignacion['id_usuario'] != MAESTRO_ID) {
    header('Location: mis_materias.php');
    exit;
}

// Obtener configuración para el parcial seleccionado
$config = $configModel->getConfiguracionConValidacion($idAsignacion, $idParcial);
$bloqueado = $config && $config['bloqueado'] == 1;

// Obtener alumnos del grupo
$alumnos = $alumnoModel->getByGrupo($asignacion['id_grupounam']);

// Obtener calificaciones existentes
$calificaciones = [];
if ($config) {
    $calificaciones = $califParcialModel->getByAsignacionParcial($idAsignacion, $idParcial);
}

// Agrupar calificaciones por alumno
$califPorAlumno = [];
foreach ($calificaciones as $calif) {
    $califPorAlumno[$calif['id_alumno']][$calif['id_aspecto']] = $calif;
}

$parciales = [1, 2, 3, 4];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluar - SIOUNAM</title>
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/components/sidebar-component.js" defer></script>
    <script src="../../assets/components/header-component.js" defer></script>
    <style>
        .evaluar-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-top: 20px;
            overflow-x: auto;
        }
        .evaluar-container table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .evaluar-container th {
            background: #f8f9fa;
            padding: 10px 12px;
            text-align: left;
            font-weight: 600;
            color: #1a237e;
            border-bottom: 2px solid #e0e0e0;
            white-space: nowrap;
        }
        .evaluar-container td {
            padding: 8px 12px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        .evaluar-container tr:hover {
            background: #f8f9fa;
        }
        .evaluar-container .input-calificacion {
            width: 60px;
            padding: 4px 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            font-size: 13px;
        }
        .evaluar-container .input-calificacion:focus {
            border-color: #1a237e;
            outline: none;
        }
        .evaluar-container .input-calificacion:disabled {
            background: #f5f5f5;
            color: #666;
        }
        .evaluar-container .input-faltas {
            width: 50px;
            padding: 4px 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            font-size: 13px;
        }
        .evaluar-container .input-faltas:focus {
            border-color: #1a237e;
            outline: none;
        }
        .evaluar-container .input-faltas:disabled {
            background: #f5f5f5;
            color: #666;
        }
        .evaluar-container .promedio-final {
            font-weight: bold;
            color: #1a237e;
        }
        .evaluar-container .promedio-final.aprobado {
            color: #2e7d32;
        }
        .evaluar-container .promedio-final.reprobado {
            color: #c62828;
        }
        .evaluar-container .btn-guardar-fila {
            background: #1a237e;
            color: white;
            border: none;
            padding: 4px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .evaluar-container .btn-guardar-fila:hover {
            background: #0d1b5e;
        }
        .evaluar-container .btn-guardar-fila:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .evaluar-container .badge-bloqueado {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .evaluar-container .badge-bloqueado i {
            margin-right: 4px;
        }
        .evaluar-container .badge-faltas {
            font-size: 12px;
            font-weight: 600;
        }
        .evaluar-container .badge-faltas.excedido {
            color: #c62828;
        }
        .evaluar-container .badge-faltas.normal {
            color: #2e7d32;
        }
        .evaluar-container .badge-faltas.alerta {
            color: #ff9800;
        }
        .btn-guardar-todo {
            background: #2e7d32;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            margin-top: 15px;
        }
        .btn-guardar-todo:hover {
            background: #1b5e20;
        }
        .btn-guardar-todo:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .parcial-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .parcial-selector .btn-parcial {
            padding: 8px 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }
        .parcial-selector .btn-parcial:hover {
            border-color: #1a237e;
        }
        .parcial-selector .btn-parcial.active {
            border-color: #1a237e;
            background: #1a237e;
            color: white;
        }
        .parcial-selector .btn-parcial.bloqueado {
            border-color: #2e7d32;
            background: #e8f5e9;
            color: #2e7d32;
        }
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        .header-actions .info {
            color: #666;
            font-size: 14px;
        }
        .header-actions .info strong {
            color: #1a237e;
        }
        .header-actions .info .bloqueado-text {
            color: #2e7d32;
            font-weight: 600;
        }
        @media (max-width: 768px) {
            .evaluar-container table {
                font-size: 11px;
            }
            .evaluar-container .input-calificacion {
                width: 45px;
                padding: 3px 4px;
            }
            .evaluar-container .input-faltas {
                width: 40px;
                padding: 3px 4px;
            }
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <sidebar-component base-path="../../"></sidebar-component>
        <div class="main-content" id="mainContent">
            <header-component title="Evaluar Alumnos" icon="check-circle">
                <div slot="actions">
                    <a href="mis_materias.php" class="btn" style="background:#1a237e;color:white;padding:8px 16px;border-radius:8px;text-decoration:none;">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </header-component>

            <div style="padding: 20px;">
                <h3 style="color: #1a237e; margin-bottom: 5px;">
                    <?php echo htmlspecialchars($asignacion['nombre_materia']); ?>
                </h3>
                <p style="color: #666; margin-top: 0;">
                    <i class="fas fa-users"></i> <?php echo htmlspecialchars($asignacion['nombre_grupo']); ?> | 
                    <i class="fas fa-university"></i> <?php echo htmlspecialchars($asignacion['nombre_carrera']); ?>
                </p>

                <!-- Selector de parcial -->
                <div class="parcial-selector">
                    <?php foreach ($parciales as $p): 
                        $pConfig = $configModel->getByAsignacionParcial($idAsignacion, $p);
                        $pBloqueado = $pConfig && $pConfig['bloqueado'] == 1;
                        $pExiste = $pConfig !== null;
                    ?>
                        <a href="?id_asignacion=<?php echo $idAsignacion; ?>&id_parcial=<?php echo $p; ?>" 
                           class="btn-parcial <?php echo $idParcial == $p ? 'active' : ''; ?> <?php echo $pBloqueado ? 'bloqueado' : ''; ?>">
                            Parcial <?php echo $p; ?>
                            <?php if ($pBloqueado): ?>
                                <i class="fas fa-lock" style="font-size:10px;"></i>
                            <?php endif; ?>
                            <?php if (!$pExiste && $idParcial == $p): ?>
                                <span style="color:#999;font-size:10px;">(sin configurar)</span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if (!$config): ?>
                    <div class="alert alert-info" style="background:#e3f2fd;color:#1565c0;padding:15px;border-radius:8px;">
                        <i class="fas fa-info-circle"></i> 
                        No has configurado la evaluación para este parcial. 
                        <a href="configurar_evaluacion.php?id_asignacion=<?php echo $idAsignacion; ?>" style="color:#1565c0;font-weight:600;">
                            Configurar ahora
                        </a>
                    </div>
                <?php elseif ($bloqueado): ?>
                    <div class="alert alert-success" style="background:#e8f5e9;color:#2e7d32;padding:15px;border-radius:8px;">
                        <i class="fas fa-lock"></i> 
                        Esta evaluación está bloqueada. No se pueden modificar las calificaciones.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info" style="background:#e3f2fd;color:#1565c0;padding:15px;border-radius:8px;">
                        <i class="fas fa-info-circle"></i> 
                        Ingresa las calificaciones para cada alumno. El sistema calculará automáticamente el promedio final.
                        <span style="display:block;margin-top:5px;font-size:13px;">
                            Límite de faltas: <strong><?php echo $config['limite_faltas']; ?></strong> faltas (20% de <?php echo $config['total_clases']; ?> clases)
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($config && !empty($alumnos)): ?>
                    <div class="evaluar-container">
                        <div class="header-actions">
                            <div class="info">
                                <strong><?php echo count($alumnos); ?></strong> alumnos | 
                                <strong><?php echo count($config['aspectos'] ?? []); ?></strong> aspectos a evaluar
                                <?php if ($bloqueado): ?>
                                    <span class="bloqueado-text"><i class="fas fa-lock"></i> BLOQUEADO</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!$bloqueado): ?>
                                <button class="btn-guardar-todo" id="btnGuardarTodo">
                                    <i class="fas fa-save"></i> Guardar Todas las Calificaciones
                                </button>
                            <?php endif; ?>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Alumno</th>
                                    <th>Matrícula</th>
                                    <?php foreach ($config['aspectos'] as $aspecto): ?>
                                        <th title="<?php echo htmlspecialchars($aspecto['nombre']); ?> (<?php echo $aspecto['porcentaje']; ?>%)">
                                            <?php echo htmlspecialchars($aspecto['nombre']); ?>
                                            <br><span style="font-weight:normal;font-size:10px;color:#666;"><?php echo $aspecto['porcentaje']; ?>%</span>
                                        </th>
                                    <?php endforeach; ?>
                                    <th>Faltas</th>
                                    <th>Promedio</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $contador = 1; ?>
                                <?php foreach ($alumnos as $alumno): 
                                    $califAlumno = $califPorAlumno[$alumno['id_alumno']] ?? [];
                                    $idCalificacion = $califAlumno['id_calificacion'] ?? 0;
                                ?>
                                    <tr data-alumno="<?php echo $alumno['id_alumno']; ?>" 
                                        data-calificacion="<?php echo $idCalificacion; ?>">
                                        <td><?php echo $contador++; ?></td>
                                        <td><?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido_paterno']); ?></td>
                                        <td><?php echo htmlspecialchars($alumno['matricula'] ?? 'N/A'); ?></td>

                                        <?php foreach ($config['aspectos'] as $aspecto): 
                                            $calif = $califAlumno[$aspecto['id_aspecto']] ?? null;
                                            $valor = $calif ? $calif['calificacion'] : '';
                                        ?>
                                            <td>
                                                <input type="number" class="input-calificacion calif-input" 
                                                       data-alumno="<?php echo $alumno['id_alumno']; ?>"
                                                       data-aspecto="<?php echo $aspecto['id_aspecto']; ?>"
                                                       data-calificacion="<?php echo $idCalificacion; ?>"
                                                       value="<?php echo $valor !== '' ? htmlspecialchars($valor) : ''; ?>"
                                                       min="0" max="10" step="0.1"
                                                       <?php echo $bloqueado ? 'disabled' : ''; ?>>
                                            </td>
                                        <?php endforeach; ?>

                                        <td>
                                            <input type="number" class="input-faltas faltas-input" 
                                                   data-alumno="<?php echo $alumno['id_alumno']; ?>"
                                                   data-calificacion="<?php echo $idCalificacion; ?>"
                                                   value="<?php echo $califAlumno['cantidad_faltas'] ?? 0; ?>"
                                                   min="0" max="20"
                                                   <?php echo $bloqueado ? 'disabled' : ''; ?>>
                                            <span class="badge-faltas <?php 
                                                $faltas = $califAlumno['cantidad_faltas'] ?? 0;
                                                $limite = $config['limite_faltas'] ?? 4;
                                                echo $faltas > $limite ? 'excedido' : ($faltas >= $limite * 0.8 ? 'alerta' : 'normal');
                                            ?>">
                                                / <?php echo $limite; ?>
                                            </span>
                                        </td>

                                        <td>
                                            <span class="promedio-final" id="promedio-<?php echo $alumno['id_alumno']; ?>">
                                                <?php 
                                                $promedio = $califAlumno['promedio_final'] ?? null;
                                                if ($promedio !== null) {
                                                    echo number_format($promedio, 2);
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?php if (!$bloqueado): ?>
                                                <button class="btn-guardar-fila btn-guardar-fila-alumno" 
                                                        data-alumno="<?php echo $alumno['id_alumno']; ?>"
                                                        data-calificacion="<?php echo $idCalificacion; ?>">
                                                    <i class="fas fa-save"></i> Guardar
                                                </button>
                                            <?php else: ?>
                                                <span class="badge-bloqueado"><i class="fas fa-lock"></i> Bloqueado</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif ($config && empty($alumnos)): ?>
                    <div class="alert alert-info" style="background:#e3f2fd;color:#1565c0;padding:15px;border-radius:8px;">
                        <i class="fas fa-info-circle"></i> No hay alumnos asignados a este grupo.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Calcular promedio automáticamente cuando cambia una calificación
            $(document).on('change', '.calif-input', function() {
                const row = $(this).closest('tr');
                const alumnoId = row.data('alumno');
                calcularPromedio(alumnoId);
            });

            // Calcular promedio para un alumno
            function calcularPromedio(alumnoId) {
                const row = $(`tr[data-alumno="${alumnoId}"]`);
                const inputs = row.find('.calif-input');
                let total = 0;
                let ponderado = 0;
                let todasCompletas = true;

                inputs.each(function() {
                    const val = parseFloat($(this).val());
                    if (isNaN(val) || val < 0 || val > 10) {
                        todasCompletas = false;
                        return false;
                    }
                    // Los porcentajes vienen del atributo title del th
                    const th = $(this).closest('td').prevAll('th').first();
                    // Buscar el porcentaje en el th
                    const header = $(this).closest('table').find('thead th').eq($(this).closest('td').index());
                    const porcentajeText = header.find('span').text().replace('%', '');
                    const porcentaje = parseFloat(porcentajeText) || 0;
                    
                    total += val * (porcentaje / 100);
                    ponderado += porcentaje;
                });

                if (todasCompletas && ponderado === 100) {
                    const promedio = Math.round(total * 100) / 100;
                    const span = row.find('.promedio-final');
                    span.text(promedio.toFixed(2));
                    span.removeClass('aprobado reprobado');
                    if (promedio >= 6) {
                        span.addClass('aprobado');
                    } else {
                        span.addClass('reprobado');
                    }
                } else {
                    row.find('.promedio-final').text('-');
                }
            }

            // Guardar calificaciones de un alumno
            $(document).on('click', '.btn-guardar-fila-alumno', function() {
                const row = $(this).closest('tr');
                const alumnoId = row.data('alumno');
                const idCalificacion = row.data('calificacion');
                const btn = $(this);
                
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                // Recolectar datos
                const calificaciones = [];
                row.find('.calif-input').each(function() {
                    const aspectoId = $(this).data('aspecto');
                    const valor = parseFloat($(this).val()) || null;
                    calificaciones.push({
                        id_aspecto: aspectoId,
                        calificacion: valor
                    });
                });

                const faltas = parseInt(row.find('.faltas-input').val()) || 0;

                const data = {
                    action: 'guardar_alumno',
                    id_asignacion: <?php echo $idAsignacion; ?>,
                    id_parcial: <?php echo $idParcial; ?>,
                    id_alumno: alumnoId,
                    id_calificacion: idCalificacion,
                    calificaciones: calificaciones,
                    faltas: faltas
                };

                $.ajax({
                    url: '../../ajax/calificaciones_parciales.php',
                    type: 'POST',
                    data: JSON.stringify(data),
                    contentType: 'application/json',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Actualizar el id_calificacion si es nuevo
                            if (response.id_calificacion) {
                                row.data('calificacion', response.id_calificacion);
                                row.find('.calif-input').data('calificacion', response.id_calificacion);
                                row.find('.faltas-input').data('calificacion', response.id_calificacion);
                            }
                            alert('Calificaciones guardadas correctamente');
                            // Recalcular promedio
                            calcularPromedio(alumnoId);
                        } else {
                            alert('Error: ' + (response.message || 'Error al guardar'));
                        }
                        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        alert('Error de conexión');
                        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
                    }
                });
            });

            // Guardar todas las calificaciones
            $('#btnGuardarTodo').click(function() {
                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando todas...');

                const promesas = [];
                $('.btn-guardar-fila-alumno').each(function() {
                    const btnFila = $(this);
                    if (!btnFila.prop('disabled')) {
                        promesas.push(new Promise((resolve) => {
                            btnFila.click();
                            setTimeout(resolve, 500);
                        }));
                    }
                });

                Promise.all(promesas).then(() => {
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Todas las Calificaciones');
                    alert('Todas las calificaciones han sido guardadas');
                });
            });

            // Actualizar límite de faltas en tiempo real
            $(document).on('change', '.faltas-input', function() {
                const faltas = parseInt($(this).val()) || 0;
                const badge = $(this).siblings('.badge-faltas');
                const limite = parseInt(badge.text().replace('/', '').trim()) || 4;
                
                badge.removeClass('excedido alerta normal');
                if (faltas > limite) {
                    badge.addClass('excedido');
                } else if (faltas >= limite * 0.8) {
                    badge.addClass('alerta');
                } else {
                    badge.addClass('normal');
                }
            });
        });

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