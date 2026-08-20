<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../models/ConfiguracionEvaluacion.php';
require_once __DIR__ . '/../../models/Asignacion.php';

$idAsignacion = isset($_GET['id_asignacion']) ? (int)$_GET['id_asignacion'] : 0;

if (!$idAsignacion) {
    header('Location: mis_materias.php');
    exit;
}

$configModel = new ConfiguracionEvaluacion();
$asignacionModel = new Asignacion();

// Verificar que la asignación pertenece al maestro
$asignacion = $asignacionModel->getById($idAsignacion);
if (!$asignacion || $asignacion['id_usuario'] != MAESTRO_ID) {
    header('Location: mis_materias.php');
    exit;
}

// Obtener configuración por parcial
$parciales = [1, 2, 3, 4];
$configuraciones = [];

foreach ($parciales as $parcial) {
    $configuraciones[$parcial] = $configModel->getConfiguracionConValidacion($idAsignacion, $parcial);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Evaluación - SIOUNAM</title>
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/components/sidebar-component.js" defer></script>
    <script src="../../assets/components/header-component.js" defer></script>
    <style>
        .config-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .config-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            border-top: 4px solid #1a237e;
        }
        .config-card.bloqueado {
            border-top-color: #2e7d32;
            opacity: 0.85;
        }
        .config-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .config-card .card-header h3 {
            margin: 0;
            color: #1a237e;
        }
        .config-card .card-header .badge-parcial {
            background: #e3f2fd;
            color: #1565c0;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .config-card .card-header .badge-bloqueado {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .config-card .card-header .badge-bloqueado i {
            margin-right: 4px;
        }
        .form-group {
            margin-bottom: 12px;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            font-size: 13px;
            color: #333;
            margin-bottom: 4px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #1a237e;
            outline: none;
        }
        .form-group input:disabled {
            background: #f5f5f5;
            color: #666;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .aspectos-container {
            margin-top: 15px;
            border-top: 1px solid #edf2f7;
            padding-top: 15px;
        }
        .aspecto-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        .aspecto-item .aspecto-nombre {
            flex: 1;
        }
        .aspecto-item .aspecto-porcentaje {
            width: 80px;
        }
        .aspecto-item .aspecto-acciones {
            display: flex;
            gap: 5px;
        }
        .aspecto-item .aspecto-acciones button {
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
            padding: 4px;
        }
        .aspecto-item .aspecto-acciones button:hover {
            color: #c62828;
        }
        .btn-add-aspecto {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px dashed #1565c0;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 13px;
            margin-top: 10px;
        }
        .btn-add-aspecto:hover {
            background: #bbdefb;
        }
        .progress-bar-container {
            margin: 15px 0;
        }
        .progress-bar {
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }
        .progress-bar .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s;
        }
        .progress-bar .progress-fill.success {
            background: #2e7d32;
        }
        .progress-bar .progress-fill.warning {
            background: #ff9800;
        }
        .progress-bar .progress-fill.danger {
            background: #c62828;
        }
        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }
        .btn-bloquear {
            background: #2e7d32;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            width: 100%;
            margin-top: 15px;
        }
        .btn-bloquear:hover {
            background: #1b5e20;
        }
        .btn-bloquear:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .btn-desbloquear {
            background: #c62828;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            width: 100%;
            margin-top: 15px;
        }
        .btn-desbloquear:hover {
            background: #b71c1c;
        }
        .btn-guardar-config {
            background: #1a237e;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            width: 100%;
            margin-top: 10px;
        }
        .btn-guardar-config:hover {
            background: #0d1b5e;
        }
        .btn-guardar-config:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .alert-info {
            background: #e3f2fd;
            color: #1565c0;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 15px;
        }
        .alert-info i {
            margin-right: 8px;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 15px;
        }
        .alert-success i {
            margin-right: 8px;
        }
        .alert-danger {
            background: #ffebee;
            color: #c62828;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 15px;
        }
        .alert-danger i {
            margin-right: 8px;
        }
        .hidden {
            display: none !important;
        }
        @media (max-width: 768px) {
            .config-container {
                grid-template-columns: 1fr;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <sidebar-component base-path="../../"></sidebar-component>
        <div class="main-content" id="mainContent">
            <header-component title="Configurar Evaluación" icon="cog">
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

                <div class="config-container">
                    <?php foreach ($parciales as $parcial): 
                        $config = $configuraciones[$parcial] ?? null;
                        $bloqueado = $config && $config['bloqueado'] == 1;
                        $configId = $config ? $config['id_configuracion'] : 0;
                        $sumaPorcentajes = $config ? ($config['suma_porcentajes'] ?? 0) : 0;
                        $porcentajeCompleto = $config ? ($config['porcentaje_completo'] ?? false) : false;
                        $totalAspectos = $config ? count($config['aspectos'] ?? []) : 0;
                    ?>
                    <div class="config-card <?php echo $bloqueado ? 'bloqueado' : ''; ?>">
                        <div class="card-header">
                            <h3>Parcial <?php echo $parcial; ?></h3>
                            <span class="badge-parcial">Parcial <?php echo $parcial; ?></span>
                            <?php if ($bloqueado): ?>
                                <span class="badge-bloqueado"><i class="fas fa-lock"></i> Bloqueado</span>
                            <?php endif; ?>
                        </div>

                        <form class="config-form" data-parcial="<?php echo $parcial; ?>" data-config="<?php echo $configId; ?>">
                            <input type="hidden" name="id_asignacion" value="<?php echo $idAsignacion; ?>">
                            <input type="hidden" name="id_parcial" value="<?php echo $parcial; ?>">
                            <input type="hidden" name="id_configuracion" value="<?php echo $configId; ?>">

                            <!-- Número de parciales -->
                            <div class="form-group">
                                <label>Número de Parciales</label>
                                <select name="numero_parciales" class="select-parciales" <?php echo $bloqueado ? 'disabled' : ''; ?>>
                                    <option value="3" <?php echo ($config && $config['numero_parciales'] == 3) ? 'selected' : ''; ?>>3 Parciales</option>
                                    <option value="4" <?php echo (!$config || $config['numero_parciales'] == 4) ? 'selected' : ''; ?>>4 Parciales</option>
                                </select>
                            </div>

                            <!-- Total de clases -->
                            <div class="form-group">
                                <label>Total de Clases en el Semestre</label>
                                <input type="number" name="total_clases" value="<?php echo $config ? $config['total_clases'] : 20; ?>" 
                                       min="1" max="40" <?php echo $bloqueado ? 'disabled' : ''; ?>>
                                <small style="color:#999;font-size:12px;">El límite de faltas será el 20% de las clases totales</small>
                            </div>

                            <!-- Límite de faltas (calculado automáticamente) -->
                            <div class="form-group" style="background:#f5f5f5;padding:8px 12px;border-radius:6px;">
                                <label>Límite de Faltas (20%)</label>
                                <span style="font-weight:bold;color:#1a237e;">
                                    <?php echo $config ? $config['limite_faltas'] : 4; ?> faltas
                                </span>
                                <small style="color:#999;font-size:12px;display:block;">Calculado automáticamente</small>
                            </div>

                            <!-- Aspectos a evaluar -->
                            <div class="aspectos-container">
                                <label style="font-weight:500;font-size:13px;color:#333;display:block;margin-bottom:10px;">
                                    Aspectos a Evaluar
                                    <span style="color:#999;font-weight:normal;font-size:12px;">
                                        (Máximo 6, los porcentajes deben sumar 100%)
                                    </span>
                                </label>

                                <div id="aspectos-list-<?php echo $parcial; ?>">
                                    <?php if ($config && $config['aspectos']): ?>
                                        <?php foreach ($config['aspectos'] as $aspecto): ?>
                                            <div class="aspecto-item" data-id="<?php echo $aspecto['id_aspecto']; ?>">
                                                <div class="aspecto-nombre">
                                                    <input type="text" name="aspecto_nombre[]" value="<?php echo htmlspecialchars($aspecto['nombre']); ?>" 
                                                           placeholder="Ej: Examen" <?php echo $bloqueado ? 'disabled' : ''; ?>>
                                                </div>
                                                <div class="aspecto-porcentaje">
                                                    <input type="number" name="aspecto_porcentaje[]" value="<?php echo $aspecto['porcentaje']; ?>" 
                                                           min="0" max="100" step="0.5" placeholder="%" <?php echo $bloqueado ? 'disabled' : ''; ?>>
                                                </div>
                                                <div class="aspecto-acciones">
                                                    <?php if (!$bloqueado): ?>
                                                        <button type="button" class="btn-remove-aspecto" title="Eliminar">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="aspecto-item" style="color:#999;font-size:13px;text-align:center;padding:15px 0;">
                                            <i class="fas fa-info-circle"></i> No hay aspectos configurados
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!$bloqueado && ($totalAspectos < 6)): ?>
                                    <button type="button" class="btn-add-aspecto" data-parcial="<?php echo $parcial; ?>">
                                        <i class="fas fa-plus"></i> Agregar Aspecto
                                    </button>
                                <?php endif; ?>
                            </div>

                            <!-- Barra de progreso -->
                            <?php if ($config && $totalAspectos > 0): ?>
                            <div class="progress-bar-container">
                                <div class="progress-bar">
                                    <div class="progress-fill <?php 
                                        echo $sumaPorcentajes == 100 ? 'success' : 
                                            ($sumaPorcentajes > 100 ? 'danger' : 'warning'); 
                                    ?>" style="width: <?php echo min($sumaPorcentajes, 100); ?>%;">
                                    </div>
                                </div>
                                <div class="progress-label">
                                    <span><?php echo number_format($sumaPorcentajes, 1); ?>%</span>
                                    <span><?php echo $sumaPorcentajes == 100 ? '✅ 100% completo' : ($sumaPorcentajes > 100 ? '⚠️ Excede 100%' : '⚠️ Falta para completar 100%'); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Botones de acción -->
                            <?php if ($bloqueado): ?>
                                <button type="button" class="btn-desbloquear" data-parcial="<?php echo $parcial; ?>" data-config="<?php echo $configId; ?>">
                                    <i class="fas fa-unlock"></i> Desbloquear Evaluación
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn-guardar-config" data-parcial="<?php echo $parcial; ?>" data-config="<?php echo $configId; ?>">
                                    <i class="fas fa-save"></i> Guardar Configuración
                                </button>
                                <?php if ($config && $porcentajeCompleto && $totalAspectos > 0): ?>
                                    <button type="button" class="btn-bloquear" data-parcial="<?php echo $parcial; ?>" data-config="<?php echo $configId; ?>">
                                        <i class="fas fa-lock"></i> Establecer Evaluación (Bloquear)
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn-bloquear" disabled style="opacity:0.5;cursor:not-allowed;">
                                        <i class="fas fa-lock"></i> Completa el 100% para bloquear
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Agregar aspecto
            $('.btn-add-aspecto').click(function() {
                const parcial = $(this).data('parcial');
                const container = $(`#aspectos-list-${parcial}`);
                
                // Verificar que no haya más de 6 aspectos
                const currentCount = container.find('.aspecto-item').length;
                if (currentCount >= 6) {
                    alert('Máximo 6 aspectos por parcial');
                    return;
                }

                const html = `
                    <div class="aspecto-item">
                        <div class="aspecto-nombre">
                            <input type="text" name="aspecto_nombre[]" placeholder="Ej: Examen" required>
                        </div>
                        <div class="aspecto-porcentaje">
                            <input type="number" name="aspecto_porcentaje[]" min="0" max="100" step="0.5" placeholder="%" required>
                        </div>
                        <div class="aspecto-acciones">
                            <button type="button" class="btn-remove-aspecto" title="Eliminar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `;
                container.append(html);
                
                // Si el contenedor tenía el mensaje de "No hay aspectos", lo removemos
                container.find('.aspecto-item:first-child').remove();
            });

            // Eliminar aspecto (solo visual, se elimina al guardar)
            $(document).on('click', '.btn-remove-aspecto', function() {
                const item = $(this).closest('.aspecto-item');
                const id = item.data('id');
                if (id) {
                    // Si tiene ID, marcar para eliminar en el backend
                    item.find('input').prop('disabled', true);
                    item.css('opacity', '0.5');
                    item.append('<input type="hidden" name="eliminar_aspecto[]" value="'+id+'">');
                    item.find('.btn-remove-aspecto').remove();
                } else {
                    // Si es nuevo, solo eliminar del DOM
                    item.remove();
                }
            });

            // Guardar configuración
            $('.btn-guardar-config').click(function() {
                const form = $(this).closest('.config-form');
                const data = form.serializeArray();
                const parcial = form.data('parcial');
                
                // Validar que los porcentajes sumen 100
                let totalPorcentaje = 0;
                form.find('input[name="aspecto_porcentaje[]"]').each(function() {
                    const val = parseFloat($(this).val()) || 0;
                    totalPorcentaje += val;
                });

                if (form.find('input[name="aspecto_porcentaje[]"]').length > 0 && totalPorcentaje !== 100) {
                    alert('Los porcentajes deben sumar exactamente 100%. Actual: ' + totalPorcentaje + '%');
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

                $.ajax({
                    url: '../../ajax/configuracion_evaluacion.php',
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Configuración guardada correctamente');
                            location.reload();
                        } else {
                            alert('Error: ' + (response.message || 'Error al guardar'));
                        }
                        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Configuración');
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        alert('Error de conexión');
                        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Configuración');
                    }
                });
            });

            // Bloquear configuración
            $('.btn-bloquear').click(function() {
                const parcial = $(this).data('parcial');
                const configId = $(this).data('config');
                
                if (!configId) {
                    alert('Primero debes guardar la configuración');
                    return;
                }

                if (!confirm('¿Estás seguro de bloquear esta configuración? Ya no podrás modificarla.')) {
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Bloqueando...');

                $.ajax({
                    url: '../../ajax/configuracion_evaluacion.php',
                    type: 'POST',
                    data: {
                        action: 'bloquear',
                        id_configuracion: configId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Evaluación bloqueada correctamente');
                            location.reload();
                        } else {
                            alert('Error al bloquear');
                        }
                        btn.prop('disabled', false).html('<i class="fas fa-lock"></i> Establecer Evaluación (Bloquear)');
                    },
                    error: function() {
                        alert('Error de conexión');
                        btn.prop('disabled', false).html('<i class="fas fa-lock"></i> Establecer Evaluación (Bloquear)');
                    }
                });
            });

            // Desbloquear configuración
            $('.btn-desbloquear').click(function() {
                const parcial = $(this).data('parcial');
                const configId = $(this).data('config');

                if (!confirm('¿Estás seguro de desbloquear esta configuración? Podrás modificarla nuevamente.')) {
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Desbloqueando...');

                $.ajax({
                    url: '../../ajax/configuracion_evaluacion.php',
                    type: 'POST',
                    data: {
                        action: 'desbloquear',
                        id_configuracion: configId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Configuración desbloqueada correctamente');
                            location.reload();
                        } else {
                            alert('Error al desbloquear');
                        }
                        btn.prop('disabled', false).html('<i class="fas fa-unlock"></i> Desbloquear Evaluación');
                    },
                    error: function() {
                        alert('Error de conexión');
                        btn.prop('disabled', false).html('<i class="fas fa-unlock"></i> Desbloquear Evaluación');
                    }
                });
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