<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../models/Alumno.php';
require_once __DIR__ . '/../../models/Carrera.php';
require_once __DIR__ . '/../../models/Grupo.php';

$alumnoModel = new Alumno();
$carreraModel = new Carrera();
$grupoModel = new Grupo();

// Obtener datos filtrados por carrera
$alumnos = $alumnoModel->getByCarrera(CARRERA_ID);
$grupos = $grupoModel->getByCarrera(CARRERA_ID);
$carrera = $carreraModel->getById(CARRERA_ID);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Alumnos - Coordinador</title>
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        .filter-section input, .filter-section select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .filter-section button {
            padding: 8px 20px;
            background: #1a237e;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .filter-section button:hover {
            background: #0d1b5e;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        .btn-migrate {
            background: #2e7d32;
            color: white;
        }
        .btn-migrate:hover {
            background: #1b5e20;
        }
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
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-group label .required {
            color: #c62828;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #1a237e;
            outline: none;
            box-shadow: 0 0 5px rgba(26, 35, 126, 0.2);
        }
        .form-group .matricula-info {
            background: #fff3e0;
            padding: 8px 12px;
            border-radius: 4px;
            color: #e65100;
            font-size: 0.85rem;
            margin-top: 5px;
            display: none;
        }
        .form-group .matricula-info.show {
            display: block;
        }
        .form-group .matricula-info.error {
            background: #ffebee;
            color: #c62828;
        }
        .form-group .matricula-info.success {
            background: #e8f5e9;
            color: #2e7d32;
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
        .btn-save:disabled {
            opacity: 0.6;
            cursor: not-allowed;
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
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .empty-state i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 15px;
        }
        .badge-matricula {
            background: #e3f2fd;
            color: #1565c0;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .badge-id {
            background: #f5f5f5;
            color: #666;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <sidebar-component></sidebar-component>
        
        <main class="main-content">
            <header-component title="Gestión de Alumnos">
                <span slot="actions">
                    <span class="badge" style="background: #1a237e; color: white; padding: 8px 16px; border-radius: 20px;">
                        <i class="fas fa-university"></i> <?php echo htmlspecialchars($carrera['nombre_carrera'] ?? 'Carrera'); ?>
                    </span>
                </span>
            </header-component>

            <div class="content-wrapper" style="padding: 20px;">
                <!-- Filtros -->
                <div class="filter-section">
                    <select id="filterGrupo" onchange="applyFilters()">
                        <option value="">Todos los grupos</option>
                        <?php foreach ($grupos as $grupo): ?>
                        <option value="<?php echo $grupo['id_grupounam']; ?>">
                            <?php echo htmlspecialchars($grupo['nombre_grupo']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="filterSearch" placeholder="Buscar por nombre o matrícula..." onkeyup="applyFilters()">
                    <button onclick="openModal('alumno')">
                        <i class="fas fa-user-plus"></i> Nuevo Alumno
                    </button>
                    <?php if (count($grupos) >= 2): ?>
                    <button class="btn-migrate" onclick="openModal('migracion')">
                        <i class="fas fa-arrows-alt-h"></i> Migrar Alumnos
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Tabla -->
                <div class="table-container">
                    <table id="alumnosTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Matrícula</th>
                                <th>Nombre</th>
                                <th>Apellidos</th>
                                <th>Grupo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($alumnos)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-user-graduate"></i>
                                        <p>No hay alumnos registrados en esta carrera.</p>
                                        <button class="btn-migrate" onclick="openModal('alumno')">
                                            <i class="fas fa-user-plus"></i> Registrar primer alumno
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($alumnos as $alumno): ?>
                            <tr data-grupo="<?php echo $alumno['id_grupounam']; ?>">
                                <td><span class="badge-id">#<?php echo $alumno['id_alumno']; ?></span></td>
                                <td>
                                    <span class="badge-matricula">
                                        <i class="fas fa-id-card"></i> <?php echo htmlspecialchars($alumno['matricula'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($alumno['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($alumno['apellido_paterno'] . ' ' . $alumno['apellido_materno']); ?></td>
                                <td><?php echo htmlspecialchars($alumno['nombre_grupo'] ?? 'Sin grupo'); ?></td>
                                <td>
                                    <button class="btn-action btn-edit" onclick="editAlumno(<?php echo $alumno['id_alumno']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-delete" onclick="deleteAlumno(<?php echo $alumno['id_alumno']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal -->
    <div class="modal" id="formModal">
        <div class="modal-content">
            <h3 id="modalTitle">Nuevo Alumno</h3>
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
        const gruposData = <?php echo json_encode($grupos); ?>;
        let matriculaTimeout = null;

        function applyFilters() {
            const grupo = document.getElementById('filterGrupo').value;
            const search = document.getElementById('filterSearch').value.toLowerCase();
            const rows = document.querySelectorAll('#alumnosTable tbody tr');
            
            rows.forEach(row => {
                let show = true;
                
                if (grupo && row.dataset.grupo != grupo) {
                    show = false;
                }
                
                if (search) {
                    const text = row.textContent.toLowerCase();
                    if (!text.includes(search)) {
                        show = false;
                    }
                }
                
                row.style.display = show ? '' : 'none';
            });
        }

        function checkMatricula(matricula, excludeId = null) {
            const info = $('#matriculaInfo');
            
            if (!matricula || matricula.length < 3) {
                info.removeClass('show error success');
                info.html('');
                $('#btnSubmit').prop('disabled', false);
                return;
            }
            
            clearTimeout(matriculaTimeout);
            matriculaTimeout = setTimeout(() => {
                $.ajax({
                    url: '../../ajax/alumnos.php',
                    type: 'POST',
                    data: {
                        action: 'check_matricula',
                        matricula: matricula,
                        exclude_id: excludeId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.exists) {
                            info.removeClass('success').addClass('show error');
                            info.html('<i class="fas fa-exclamation-circle"></i> Esta matrícula ya está registrada');
                            $('#btnSubmit').prop('disabled', true);
                        } else {
                            info.removeClass('error').addClass('show success');
                            info.html('<i class="fas fa-check-circle"></i> Matrícula disponible');
                            $('#btnSubmit').prop('disabled', false);
                        }
                    },
                    error: function() {
                        info.removeClass('show error success');
                        info.html('');
                    }
                });
            }, 500);
        }

        function openModal(type, data = null) {
            const modal = document.getElementById('formModal');
            const title = document.getElementById('modalTitle');
            const form = document.getElementById('formModalContent');
            
            // Determinar si es edición (data no es null)
            const isEdit = data !== null;
            
            let html = '';
            
            if (type === 'alumno') {
                title.textContent = isEdit ? 'Editar Alumno' : 'Nuevo Alumno';
                
                // Si es edición, mostrar el ID
                const idDisplay = isEdit ? 
                    `<div class="form-group">
                        <label>ID del Alumno</label>
                        <div style="background: #f5f5f5; padding: 8px 12px; border-radius: 4px; color: #666;">
                            <i class="fas fa-hashtag"></i> ${data.id_alumno}
                            <span style="font-size: 0.8rem; margin-left: 10px; color: #999;">(Autogenerado, no editable)</span>
                        </div>
                    </div>` : '';
                
                html = `
                    <input type="hidden" name="action" value="${isEdit ? 'update' : 'create'}">
                    <input type="hidden" name="id" value="${isEdit ? data.id_alumno : ''}">
                    <input type="hidden" name="id_carrera" value="${CARRERA_ID}">
                    
                    ${idDisplay}
                    
                    <div class="form-group">
                        <label>Matrícula <span class="required">*</span></label>
                        <input type="text" name="matricula" id="matriculaInput" 
                               required value="${isEdit ? (data.matricula || '') : ''}" 
                               placeholder="Ej: A20240001"
                               onkeyup="checkMatricula(this.value, ${isEdit ? data.id_alumno : 'null'})">
                        <div id="matriculaInfo" class="matricula-info"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Nombre <span class="required">*</span></label>
                        <input type="text" name="nombre" required value="${isEdit ? data.nombre : ''}" placeholder="Nombre del alumno">
                    </div>
                    <div class="form-group">
                        <label>Apellido Paterno <span class="required">*</span></label>
                        <input type="text" name="apellido_paterno" required value="${isEdit ? data.apellido_paterno : ''}" placeholder="Apellido paterno">
                    </div>
                    <div class="form-group">
                        <label>Apellido Materno <span class="required">*</span></label>
                        <input type="text" name="apellido_materno" required value="${isEdit ? data.apellido_materno : ''}" placeholder="Apellido materno">
                    </div>
                    <div class="form-group">
                        <label>Grupo <span class="required">*</span></label>
                        <select name="id_grupounam" required>
                            <option value="">Seleccionar</option>
                            ${gruposData.map(g => 
                                `<option value="${g.id_grupounam}" ${isEdit && data.id_grupounam == g.id_grupounam ? 'selected' : ''}>
                                    ${g.nombre_grupo}
                                </option>`
                            ).join('')}
                        </select>
                    </div>
                    <div style="text-align: right; margin-top: 20px;">
                        <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                        <button type="submit" class="btn-save" id="btnSubmit">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                `;
            } else if (type === 'migracion') {
                title.textContent = 'Migrar Alumnos';
                html = `
                    <input type="hidden" name="action" value="migrar">
                    <div class="form-group">
                        <label>Grupo de Origen <span class="required">*</span></label>
                        <select name="grupo_origen" id="grupoOrigen" required>
                            <option value="">Seleccionar</option>
                            ${gruposData.map(g => 
                                `<option value="${g.id_grupounam}">${g.nombre_grupo}</option>`
                            ).join('')}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Grupo de Destino <span class="required">*</span></label>
                        <select name="grupo_destino" id="grupoDestino" required>
                            <option value="">Seleccionar</option>
                            ${gruposData.map(g => 
                                `<option value="${g.id_grupounam}">${g.nombre_grupo}</option>`
                            ).join('')}
                        </select>
                    </div>
                    <div id="migrateInfo" style="background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; display: none;">
                        <i class="fas fa-info-circle" style="color: #ff9800;"></i>
                        <span id="migrateCount">0</span> alumnos serán migrados.
                    </div>
                    <div style="text-align: right; margin-top: 20px;">
                        <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                        <button type="submit" class="btn-save">
                            <i class="fas fa-arrows-alt-h"></i> Migrar
                        </button>
                    </div>
                `;
            }
            
            form.innerHTML = html;
            modal.classList.add('active');
            
            // Event listeners para migración
            if (type === 'migracion') {
                $('#grupoOrigen, #grupoDestino').on('change', function() {
                    const origen = $('#grupoOrigen').val();
                    const destino = $('#grupoDestino').val();
                    if (origen && destino && origen !== destino) {
                        const count = document.querySelectorAll(`#alumnosTable tbody tr[data-grupo="${origen}"]`).length;
                        $('#migrateInfo').show();
                        $('#migrateCount').text(count);
                    } else {
                        $('#migrateInfo').hide();
                    }
                });
            }
            
            // Si es edición y hay matrícula, validarla al cargar
            if (type === 'alumno' && isEdit && data.matricula) {
                setTimeout(() => {
                    checkMatricula(data.matricula, data.id_alumno);
                }, 100);
            }
            
            // Manejar submit del formulario
            form.onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                let url = '../../ajax/alumnos.php';
                
                // Si es migración, validar
                if (formData.get('action') === 'migrar') {
                    const origen = formData.get('grupo_origen');
                    const destino = formData.get('grupo_destino');
                    if (origen === destino) {
                        alert('Los grupos de origen y destino deben ser diferentes.');
                        return;
                    }
                    if (!confirm('¿Estás seguro de migrar los alumnos del grupo seleccionado?')) {
                        return;
                    }
                }
                
                // Deshabilitar botón para evitar envíos múltiples
                const submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message || 'Operación completada correctamente.');
                            location.reload();
                        } else {
                            alert('Error: ' + (response.message || 'Ocurrió un error'));
                            submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        alert('Error de conexión. Revisa la consola para más detalles.');
                        submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
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

        function editAlumno(id) {
            $.ajax({
                url: '../../ajax/alumnos.php',
                type: 'POST',
                data: { action: 'get', id: id },
                dataType: 'json',
                success: function(data) {
                    openModal('alumno', data);
                },
                error: function() {
                    alert('Error al obtener los datos del alumno.');
                }
            });
        }
        

        function deleteAlumno(id) {
            if (!confirm('¿Eliminar este alumno?')) return;
            $.ajax({
                url: '../../ajax/alumnos.php',
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message || 'Alumno eliminado correctamente.');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Ocurrió un error'));
                    }
                },
                error: function() {
                    alert('Error de conexión.');
                }
            });
        }

        // Debug
        console.log('Carrera ID:', CARRERA_ID);
        console.log('Grupos:', gruposData);
    </script>
</body>
</html>