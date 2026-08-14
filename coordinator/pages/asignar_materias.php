<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../models/Materia.php';
require_once __DIR__ . '/../../models/Grupo.php';
require_once __DIR__ . '/../../models/Asignacion.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Carrera.php';

$materiaModel = new Materia();
$grupoModel = new Grupo();
$asignacionModel = new Asignacion();
$userModel = new User();
$carreraModel = new Carrera();

$carrera = $carreraModel->getById(CARRERA_ID);

// Obtener datos filtrados por carrera
$materias = $materiaModel->getByCarrera(CARRERA_ID);
$grupos = $grupoModel->getByCarrera(CARRERA_ID);
$asignaciones = $asignacionModel->getByCarrera(CARRERA_ID);
$maestros = $userModel->getMaestrosByCarrera(CARRERA_ID);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Materias - Coordinador</title>
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .tab-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .tabs {
            display: flex;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 20px;
            gap: 5px;
        }
        .tab {
            padding: 12px 24px;
            cursor: pointer;
            border: none;
            background: none;
            color: #666;
            font-weight: 600;
            transition: all 0.3s ease;
            border-radius: 5px 5px 0 0;
        }
        .tab:hover {
            background: #f5f5f5;
            color: #1a237e;
        }
        .tab.active {
            background: #1a237e;
            color: white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .table-container {
            overflow-x: auto;
        }
        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-container th {
            background: #f5f5f5;
            padding: 10px;
            text-align: left;
            font-weight: 600;
        }
        .table-container td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .btn-action {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin: 2px;
        }
        .btn-edit {
            background: #1565c0;
            color: white;
        }
        .btn-edit:hover {
            background: #0d47a1;
        }
        .btn-delete {
            background: #c62828;
            color: white;
        }
        .btn-delete:hover {
            background: #b71c1c;
        }
        .btn-assign {
            background: #2e7d32;
            color: white;
        }
        .btn-assign:hover {
            background: #1b5e20;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <sidebar-component></sidebar-component>
        
        <main class="main-content">
            <header-component title="Asignar Materias">
                <span slot="actions">
                    <span class="badge" style="background: #1a237e; color: white; padding: 8px 16px; border-radius: 20px;">
                        <i class="fas fa-university"></i> <?php echo htmlspecialchars($carrera['nombre_carrera'] ?? 'Carrera'); ?>
                    </span>
                </span>
            </header-component>

            <div class="content-wrapper" style="padding: 20px;">
                <div class="tab-container">
                    <!-- Tabs -->
                    <div class="tabs">
                        <button class="tab active" data-tab="materias">Materias</button>
                        <button class="tab" data-tab="grupos">Grupos</button>
                        <button class="tab" data-tab="asignaciones">Asignaciones</button>
                    </div>

                    <!-- Tab Materias -->
                    <div class="tab-content active" id="tab-materias">
                        <h3><i class="fas fa-book"></i> Gestión de Materias</h3>
                        <button class="btn-action btn-assign" onclick="openModal('materia')">
                            <i class="fas fa-plus"></i> Nueva Materia
                        </button>
                        <div class="table-container" style="margin-top: 15px;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Carrera</th>
                                        <th>Semestre</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($materias as $materia): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($materia['nombre_materia']); ?></td>
                                        <td><?php echo htmlspecialchars($materia['nombre_carrera']); ?></td>
                                        <td><?php echo htmlspecialchars($materia['nombre_semestre']); ?></td>
                                        <td>
                                            <button class="btn-action btn-edit" onclick="editMateria(<?php echo $materia['id_materia']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" onclick="deleteMateria(<?php echo $materia['id_materia']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Grupos -->
                    <div class="tab-content" id="tab-grupos">
                        <h3><i class="fas fa-layer-group"></i> Gestión de Grupos</h3>
                        <button class="btn-action btn-assign" onclick="openModal('grupo')">
                            <i class="fas fa-plus"></i> Nuevo Grupo
                        </button>
                        <div class="table-container" style="margin-top: 15px;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Carrera</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grupos as $grupo): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($grupo['nombre_grupo']); ?></td>
                                        <td><?php echo htmlspecialchars($grupo['nombre_carrera']); ?></td>
                                        <td>
                                            <button class="btn-action btn-edit" onclick="editGrupo(<?php echo $grupo['id_grupounam']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" onclick="deleteGrupo(<?php echo $grupo['id_grupounam']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Asignaciones -->
                    <div class="tab-content" id="tab-asignaciones">
                        <h3><i class="fas fa-tasks"></i> Asignaciones Maestro-Materia-Grupo</h3>
                        <button class="btn-action btn-assign" onclick="openModal('asignacion')">
                            <i class="fas fa-plus"></i> Nueva Asignación
                        </button>
                        <?php if (!empty($asignaciones)): ?>
                        <button class="btn-action btn-delete" onclick="deleteAllAsignaciones()" style="margin-left: 10px;">
                            <i class="fas fa-trash"></i> Desasignar Todas
                        </button>
                        <?php endif; ?>
                        <div class="table-container" style="margin-top: 15px;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Grupo</th>
                                        <th>Materia</th>
                                        <th>Maestro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($asignaciones as $asignacion): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($asignacion['nombre_grupo']); ?></td>
                                        <td><?php echo htmlspecialchars($asignacion['nombre_materia']); ?></td>
                                        <td><?php echo htmlspecialchars($asignacion['nombre_maestro']); ?></td>
                                        <td>
                                            <button class="btn-action btn-delete" onclick="deleteAsignacion(<?php echo $asignacion['id_asignacion']; ?>)">
                                                <i class="fas fa-user-minus"></i> Desasignar
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal -->
    <div class="modal" id="formModal">
        <div class="modal-content">
            <h3 id="modalTitle">Nuevo</h3>
            <form id="formModalContent">
                <!-- Contenido dinámico -->
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/components/sidebar-component.js"></script>
    <script src="../../assets/components/header-component.js"></script>
    
    <script>
        const CARRERA_ID = <?php echo CARRERA_ID; ?>;
        
        // Tabs
        $('.tab').on('click', function() {
            $('.tab').removeClass('active');
            $(this).addClass('active');
            const tab = $(this).data('tab');
            $('.tab-content').removeClass('active');
            $('#tab-' + tab).addClass('active');
        });

        // Abrir modal
        function openModal(type, data = null) {
            const modal = document.getElementById('formModal');
            const title = document.getElementById('modalTitle');
            const form = document.getElementById('formModalContent');
            
            let html = '';
            
            switch(type) {
                case 'materia':
                    title.textContent = data ? 'Editar Materia' : 'Nueva Materia';
                    html = `
                        <input type="hidden" name="action" value="${data ? 'update' : 'create'}">
                        <input type="hidden" name="id" value="${data ? data.id_materia : ''}">
                        <div class="form-group">
                            <label>Nombre de la Materia</label>
                            <input type="text" name="nombre_materia" required value="${data ? data.nombre_materia : ''}">
                        </div>
                        <div class="form-group">
                            <label>Carrera</label>
                            <select name="id_carrera" required>
                                <option value="${CARRERA_ID}" selected>${<?php echo json_encode($carrera['nombre_carrera']); ?>}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Semestre</label>
                            <select name="id_semestre" required>
                                <option value="">Seleccionar</option>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>" ${data && data.id_semestre == <?php echo $i; ?> ? 'selected' : ''}>
                                    <?php echo $i; ?>er Semestre
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div style="text-align: right; margin-top: 20px;">
                            <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                            <button type="submit" class="btn-save">Guardar</button>
                        </div>
                    `;
                    break;
                    
                case 'grupo':
                    title.textContent = data ? 'Editar Grupo' : 'Nuevo Grupo';
                    html = `
                        <input type="hidden" name="action" value="${data ? 'update' : 'create'}">
                        <input type="hidden" name="id" value="${data ? data.id_grupounam : ''}">
                        <div class="form-group">
                            <label>Nombre del Grupo</label>
                            <input type="text" name="nombre_grupo" required value="${data ? data.nombre_grupo : ''}">
                        </div>
                        <div class="form-group">
                            <label>Carrera</label>
                            <select name="id_carrera" required>
                                <option value="${CARRERA_ID}" selected>${<?php echo json_encode($carrera['nombre_carrera']); ?>}</option>
                            </select>
                        </div>
                        <div style="text-align: right; margin-top: 20px;">
                            <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                            <button type="submit" class="btn-save">Guardar</button>
                        </div>
                    `;
                    break;
                    
                case 'asignacion':
                    title.textContent = 'Nueva Asignación';
                    html = `
                        <input type="hidden" name="action" value="create">
                        <div class="form-group">
                            <label>Grupo</label>
                            <select name="id_grupounam" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($grupos as $grupo): ?>
                                <option value="<?php echo $grupo['id_grupounam']; ?>">
                                    <?php echo htmlspecialchars($grupo['nombre_grupo']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Materia</label>
                            <select name="id_materia" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($materias as $materia): ?>
                                <option value="<?php echo $materia['id_materia']; ?>">
                                    <?php echo htmlspecialchars($materia['nombre_materia']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Maestro</label>
                            <select name="id_usuario" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($maestros as $maestro): ?>
                                <option value="<?php echo $maestro['id_usuario']; ?>">
                                    <?php echo htmlspecialchars($maestro['nombre'] . ' ' . $maestro['apellido_paterno']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="text-align: right; margin-top: 20px;">
                            <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                            <button type="submit" class="btn-save">Guardar</button>
                        </div>
                    `;
                    break;
            }
            
            form.innerHTML = html;
            modal.classList.add('active');
            
            // Manejar submit del formulario
            form.onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('id_carrera', CARRERA_ID);
                
                const action = formData.get('action');
                let url = '../../ajax/materias.php';
                let type = formData.get('action') === 'create' ? 'create' : 'update';
                
                if (type === 'create' || type === 'update') {
                    // Procesar según el tipo
                    if (this.querySelector('input[name="nombre_materia"]')) {
                        url = '../../ajax/materias.php';
                    } else if (this.querySelector('input[name="nombre_grupo"]')) {
                        url = '../../ajax/grupos.php';
                    } else {
                        url = '../../ajax/asignaciones.php';
                    }
                }
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success || response === true) {
                            alert('Operación completada correctamente.');
                            location.reload();
                        } else {
                            alert('Error: ' + (response.message || 'Ocurrió un error'));
                        }
                    },
                    error: function() {
                        alert('Error de conexión.');
                    }
                });
            };
        }

        function closeModal() {
            document.getElementById('formModal').classList.remove('active');
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('formModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Funciones CRUD
        function editMateria(id) {
            $.ajax({
                url: '../../ajax/materias.php',
                type: 'POST',
                data: { action: 'get', id: id },
                dataType: 'json',
                success: function(data) {
                    openModal('materia', data);
                }
            });
        }

        function deleteMateria(id) {
            if (!confirm('¿Eliminar esta materia?')) return;
            $.ajax({
                url: '../../ajax/materias.php',
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success || response === true) {
                        location.reload();
                    }
                }
            });
        }

        function editGrupo(id) {
            $.ajax({
                url: '../../ajax/grupos.php',
                type: 'POST',
                data: { action: 'get', id: id },
                dataType: 'json',
                success: function(data) {
                    openModal('grupo', data);
                }
            });
        }

        function deleteGrupo(id) {
            if (!confirm('¿Eliminar este grupo?')) return;
            $.ajax({
                url: '../../ajax/grupos.php',
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success || response === true) {
                        location.reload();
                    }
                }
            });
        }

        function deleteAsignacion(id) {
            if (!confirm('¿Desasignar esta asignación?')) return;
            $.ajax({
                url: '../../ajax/asignaciones.php',
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success || response === true) {
                        location.reload();
                    }
                }
            });
        }

        function deleteAllAsignaciones() {
            if (!confirm('¿Desasignar TODAS las asignaciones?')) return;
            $.ajax({
                url: '../../ajax/asignaciones.php',
                type: 'POST',
                data: { action: 'delete_all' },
                dataType: 'json',
                success: function(response) {
                    if (response.success || response === true) {
                        location.reload();
                    }
                }
            });
        }

        // Estilos para el modal
        const styleModal = document.createElement('style');
        styleModal.textContent = `
            .modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 2000;
                justify-content: center;
                align-items: center;
            }
            .modal.active {
                display: flex;
            }
            .modal-content {
                background: white;
                padding: 30px;
                border-radius: 10px;
                width: 90%;
                max-width: 500px;
                max-height: 90vh;
                overflow-y: auto;
            }
            .modal-content h3 {
                margin-top: 0;
                color: #1a237e;
            }
            .btn-save {
                background: #1a237e;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
            }
            .btn-save:hover {
                background: #0d1b5e;
            }
            .btn-cancel {
                background: #ccc;
                color: #333;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                margin-right: 10px;
            }
            .btn-cancel:hover {
                background: #bbb;
            }
        `;
        document.head.appendChild(styleModal);
    </script>
</body>
</html>