<?php
// admin/pages/gestion_alumnos.php
require_once '../includes/auth_check.php';
require_once __DIR__ . '/../../models/Alumno.php';
require_once __DIR__ . '/../../models/Carrera.php';
require_once __DIR__ . '/../../models/Grupo.php';

$alumnoModel = new Alumno();
$carreraModel = new Carrera();
$grupoModel = new Grupo();

// Obtener datos
$alumnos = $alumnoModel->getAll();
$carreras = $carreraModel->getAll();
$grupos = $grupoModel->getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Alumnos - SIOUNAM</title>
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/components/sidebar-component.js" defer></script>
    <script src="../../assets/components/header-component.js" defer></script>
    <style>
        .filter-section {
            background: #fff;
            padding: 15px 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            align-items: end;
        }
        .filter-section .form-group {
            margin-bottom: 0;
        }
        .filter-section .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .filter-section .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .filter-section .form-control:focus {
            border-color: #1a237e;
            outline: none;
        }
        .btn-search {
            background: #1a237e;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            height: 38px;
            font-weight: 500;
        }
        .btn-search:hover {
            background: #0d1b5e;
        }
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            overflow-x: auto;
        }
        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-container th {
            background: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #1a237e;
            border-bottom: 2px solid #e0e0e0;
        }
        .table-container td {
            padding: 10px 15px;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
        }
        .table-container tr:hover {
            background: #f8f9fa;
        }
        .btn-action {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 2px;
            transition: all 0.2s;
        }
        .btn-action:hover {
            transform: scale(1.05);
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
        .btn-primary {
            background: #1a237e;
            color: white;
        }
        .btn-primary:hover {
            background: #0d1b5e;
        }
        .btn-warning {
            background: #ff9800;
            color: white;
        }
        .btn-warning:hover {
            background: #e68900;
        }
        .btn-sm {
            padding: 5px 12px;
            font-size: 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: #fff;
            border-radius: 15px;
            max-width: 550px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .modal-header h3 {
            margin: 0;
            color: #1a237e;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
        }
        .modal-close:hover {
            color: #c62828;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }
        .form-group label .required {
            color: #c62828;
        }
        .form-group .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        .form-group .form-control:focus {
            border-color: #1a237e;
            outline: none;
            box-shadow: 0 0 5px rgba(26,35,126,0.2);
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
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
            border-top: 1px solid #edf2f7;
            padding-top: 20px;
        }
        .btn-save {
            background: #1a237e;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }
        .btn-save:hover {
            background: #0d1b5e;
        }
        .btn-save:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .btn-cancel {
            background: #e0e0e0;
            color: #333;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }
        .btn-cancel:hover {
            background: #ccc;
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
        .alert-box {
            background: #fff3cd;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
        }
        .alert-box p {
            margin: 0;
            font-size: 13px;
            color: #856404;
        }
        .alert-box i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <sidebar-component base-path="../../"></sidebar-component>
        <div class="main-content" id="mainContent">
            <header-component title="Gestión de Alumnos" icon="user-graduate">
                <div slot="actions">
                    <button class="btn btn-primary btn-sm" onclick="abrirModalAlumno()">
                        <i class="fas fa-plus"></i> Nuevo Alumno
                    </button>
                    <button class="btn btn-warning btn-sm" onclick="abrirModalMigracion()">
                        <i class="fas fa-arrow-right"></i> Migrar Alumnos
                    </button>
                </div>
            </header-component>

            <div style="padding: 20px;">
                <!-- Filtros -->
                <div class="filter-section">
                    <div class="form-group">
                        <label>Carrera</label>
                        <select class="form-control" id="filtroCarrera" onchange="filtrarAlumnos()">
                            <option value="">Todas</option>
                            <?php foreach ($carreras as $carrera): ?>
                            <option value="<?php echo $carrera['id_carrera']; ?>">
                                <?php echo htmlspecialchars($carrera['nombre_carrera']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Grupo</label>
                        <select class="form-control" id="filtroGrupo" onchange="filtrarAlumnos()">
                            <option value="">Todos</option>
                            <?php foreach ($grupos as $grupo): ?>
                            <option value="<?php echo $grupo['id_grupounam']; ?>">
                                <?php echo htmlspecialchars($grupo['nombre_grupo']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Buscar</label>
                        <input type="text" class="form-control" id="buscarAlumno" placeholder="Matrícula o nombre..." onkeyup="filtrarAlumnos()">
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button class="btn-search" onclick="filtrarAlumnos()">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
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
                                <th>Carrera</th>
                                <th>Grupo</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="alumnosBody">
                            <!-- Cargado por JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Alumno -->
    <div class="modal" id="modalAlumno">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalAlumnoTitle">Nuevo Alumno</h3>
                <button class="modal-close" onclick="cerrarModal('modalAlumno')">&times;</button>
            </div>
            
            <form id="frmAlumno">
                <input type="hidden" id="id_alumno" name="id_alumno">
                <input type="hidden" id="editando" value="false">
                
                <div class="form-group">
                    <label>Matrícula <span class="required">*</span></label>
                    <input type="text" class="form-control" id="matricula_alumno" name="matricula" 
                           placeholder="Ej: A20240001" onkeyup="checkMatricula(this.value)">
                    <div id="matriculaInfo" class="matricula-info"></div>
                </div>
                
                <div class="form-group">
                    <label>Carrera <span class="required">*</span></label>
                    <select class="form-control" id="id_carrera_alumno" name="id_carrera" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($carreras as $carrera): ?>
                        <option value="<?php echo $carrera['id_carrera']; ?>">
                            <?php echo htmlspecialchars($carrera['nombre_carrera']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Grupo <span class="required">*</span></label>
                    <select class="form-control" id="id_grupounam_alumno" name="id_grupounam" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($grupos as $grupo): ?>
                        <option value="<?php echo $grupo['id_grupounam']; ?>">
                            <?php echo htmlspecialchars($grupo['nombre_grupo']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre <span class="required">*</span></label>
                        <input type="text" class="form-control" id="nombre_alumno" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Apellido Paterno <span class="required">*</span></label>
                        <input type="text" class="form-control" id="apellido_paterno_alumno" name="apellido_paterno" required>
                    </div>
                    <div class="form-group">
                        <label>Apellido Materno <span class="required">*</span></label>
                        <input type="text" class="form-control" id="apellido_materno_alumno" name="apellido_materno" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalAlumno')">Cancelar</button>
                    <button type="button" class="btn-save" id="btnGuardarAlumno">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Migración -->
    <div class="modal" id="modalMigracion">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Migrar Alumnos</h3>
                <button class="modal-close" onclick="cerrarModal('modalMigracion')">&times;</button>
            </div>
            
            <form id="frmMigracion">
                <div class="form-group">
                    <label>Carrera <span class="required">*</span></label>
                    <select class="form-control" id="migracion_carrera" required onchange="cargarGruposMigracion(this.value)">
                        <option value="">Seleccionar</option>
                        <?php foreach ($carreras as $carrera): ?>
                        <option value="<?php echo $carrera['id_carrera']; ?>">
                            <?php echo htmlspecialchars($carrera['nombre_carrera']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Grupo Origen <span class="required">*</span></label>
                    <select class="form-control" id="migracion_grupo_origen" required></select>
                </div>
                
                <div class="form-group">
                    <label>Grupo Destino <span class="required">*</span></label>
                    <select class="form-control" id="migracion_grupo_destino" required></select>
                </div>
                
                <div class="alert-box">
                    <i class="fas fa-exclamation-triangle"></i>
                    Esta acción moverá todos los alumnos del grupo origen al grupo destino.
                    Los alumnos conservarán sus calificaciones.
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalMigracion')">Cancelar</button>
                    <button type="button" class="btn-save" id="btnMigrar" style="background: #ff9800;">Migrar Alumnos</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let editandoAlumno = false;
        let matriculaTimeout = null;
        let todosLosAlumnos = [];

        $(document).ready(function() {
            cargarAlumnos();

            // Guardar alumno
            $('#btnGuardarAlumno').click(guardarAlumno);
            
            // Migrar alumnos
            $('#btnMigrar').click(migrarAlumnos);

            // Cargar grupos para migración al cambiar carrera
            $('#migracion_carrera').change(function() {
                cargarGruposMigracion($(this).val());
            });
        });

        function cargarAlumnos() {
            $.ajax({
                url: '../../ajax/alumnos.php',
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                success: function(data) {
                    todosLosAlumnos = data.map(a => ({
                        ...a,
                        nombre_completo: `${a.nombre} ${a.apellido_paterno} ${a.apellido_materno}`
                    }));
                    filtrarAlumnos();
                },
                error: function() {
                    alert('Error al cargar alumnos');
                }
            });
        }

        function cargarGruposMigracion(idCarrera) {
            if (!idCarrera) {
                $('#migracion_grupo_origen').html('<option value="">Seleccionar</option>');
                $('#migracion_grupo_destino').html('<option value="">Seleccionar</option>');
                return;
            }
            
            $.ajax({
                url: '../../ajax/grupos.php',
                type: 'POST',
                data: { action: 'list', id_carrera: idCarrera },
                dataType: 'json',
                success: function(data) {
                    let html = '<option value="">Seleccionar</option>';
                    data.forEach(function(g) {
                        html += `<option value="${g.id_grupounam}">${g.nombre_grupo}</option>`;
                    });
                    $('#migracion_grupo_origen').html(html);
                    $('#migracion_grupo_destino').html(html);
                }
            });
        }

        function filtrarAlumnos() {
            const idCarrera = $('#filtroCarrera').val();
            const idGrupo = $('#filtroGrupo').val();
            const busqueda = $('#buscarAlumno').val().toLowerCase();
            
            let filtered = todosLosAlumnos;
            
            if (idCarrera) {
                filtered = filtered.filter(a => a.id_carrera == idCarrera);
            }
            if (idGrupo) {
                filtered = filtered.filter(a => a.id_grupounam == idGrupo);
            }
            if (busqueda) {
                filtered = filtered.filter(a => 
                    (a.matricula && a.matricula.toLowerCase().includes(busqueda)) ||
                    a.nombre.toLowerCase().includes(busqueda) ||
                    a.apellido_paterno.toLowerCase().includes(busqueda) ||
                    a.apellido_materno.toLowerCase().includes(busqueda) ||
                    `${a.nombre} ${a.apellido_paterno}`.toLowerCase().includes(busqueda) ||
                    a.id_alumno.toString().includes(busqueda)
                );
            }
            
            renderTabla(filtered);
        }

        function renderTabla(data) {
            const tbody = document.getElementById('alumnosBody');
            
            if (!data || data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-user-graduate"></i>
                                <p>No hay alumnos registrados</p>
                                <button class="btn-primary btn-sm" onclick="abrirModalAlumno()">
                                    <i class="fas fa-plus"></i> Registrar primer alumno
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            data.forEach(function(alumno) {
                html += `
                    <tr>
                        <td><span class="badge-id">#${alumno.id_alumno}</span></td>
                        <td><span class="badge-matricula"><i class="fas fa-id-card"></i> ${alumno.matricula || 'N/A'}</span></td>
                        <td>${alumno.nombre}</td>
                        <td>${alumno.apellido_paterno} ${alumno.apellido_materno}</td>
                        <td>${alumno.nombre_carrera || 'N/A'}</td>
                        <td>${alumno.nombre_grupo || 'N/A'}</td>
                        <td style="text-align: center;">
                            <button class="btn-action btn-edit" onclick="editarAlumno(${alumno.id_alumno})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action btn-delete" onclick="eliminarAlumno(${alumno.id_alumno})">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn-action" style="background:#17a2b8;color:white;" onclick="verCalificaciones(${alumno.id_alumno})">
                                <i class="fas fa-star"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
        }

        function checkMatricula(matricula) {
            const info = $('#matriculaInfo');
            
            if (!matricula || matricula.length < 3) {
                info.removeClass('show error success');
                info.html('');
                $('#btnGuardarAlumno').prop('disabled', false);
                return;
            }
            
            clearTimeout(matriculaTimeout);
            matriculaTimeout = setTimeout(() => {
                const excludeId = editandoAlumno ? $('#id_alumno').val() : null;
                
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
                            $('#btnGuardarAlumno').prop('disabled', true);
                        } else {
                            info.removeClass('error').addClass('show success');
                            info.html('<i class="fas fa-check-circle"></i> Matrícula disponible');
                            $('#btnGuardarAlumno').prop('disabled', false);
                        }
                    }
                });
            }, 500);
        }

        function abrirModalAlumno() {
            editandoAlumno = false;
            document.getElementById('modalAlumnoTitle').textContent = 'Nuevo Alumno';
            document.getElementById('frmAlumno').reset();
            document.getElementById('id_alumno').value = '';
            $('#matriculaInfo').removeClass('show error success').html('');
            $('#btnGuardarAlumno').prop('disabled', false);
            document.getElementById('modalAlumno').classList.add('active');
        }

        function editarAlumno(id) {
            editandoAlumno = true;
            document.getElementById('modalAlumnoTitle').textContent = 'Editar Alumno';
            
            $.ajax({
                url: '../../ajax/alumnos.php',
                type: 'POST',
                data: { action: 'get', id: id },
                dataType: 'json',
                success: function(data) {
                    $('#id_alumno').val(data.id_alumno);
                    $('#matricula_alumno').val(data.matricula || '');
                    $('#id_carrera_alumno').val(data.id_carrera);
                    $('#id_grupounam_alumno').val(data.id_grupounam);
                    $('#nombre_alumno').val(data.nombre);
                    $('#apellido_paterno_alumno').val(data.apellido_paterno);
                    $('#apellido_materno_alumno').val(data.apellido_materno);
                    
                    setTimeout(() => {
                        checkMatricula(data.matricula || '');
                    }, 100);
                    
                    document.getElementById('modalAlumno').classList.add('active');
                },
                error: function() {
                    alert('Error al cargar los datos del alumno');
                }
            });
        }

        function guardarAlumno() {
            const data = {
                action: editandoAlumno ? 'update' : 'create',
                id: $('#id_alumno').val(),
                matricula: $('#matricula_alumno').val(),
                id_carrera: $('#id_carrera_alumno').val(),
                id_grupounam: $('#id_grupounam_alumno').val(),
                nombre: $('#nombre_alumno').val(),
                apellido_paterno: $('#apellido_paterno_alumno').val(),
                apellido_materno: $('#apellido_materno_alumno').val()
            };

            if (!data.matricula || !data.id_carrera || !data.id_grupounam || !data.nombre) {
                alert('Todos los campos son obligatorios');
                return;
            }

            const btn = $('#btnGuardarAlumno');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

            $.ajax({
                url: '../../ajax/alumnos.php',
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(editandoAlumno ? 'Alumno actualizado correctamente' : 'Alumno creado correctamente');
                        cerrarModal('modalAlumno');
                        cargarAlumnos();
                    } else {
                        alert('Error: ' + (response.message || 'Error al guardar'));
                    }
                    btn.prop('disabled', false).html('Guardar');
                },
                error: function() {
                    alert('Error de conexión');
                    btn.prop('disabled', false).html('Guardar');
                }
            });
        }

        function eliminarAlumno(id) {
            if (!id) {
                alert('ID de alumno no válido');
                return;
            }
            
            if (!confirm('¿Estás seguro de que deseas eliminar este alumno? Esta acción no se puede deshacer.')) {
                return;
            }
            
            $.ajax({
                url: '../../ajax/alumnos.php',
                type: 'POST',
                data: { 
                    action: 'delete', 
                    id: id 
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Alumno eliminado correctamente');
                        cargarAlumnos();
                    } else {
                        alert('Error al eliminar: ' + (response.message || 'Error desconocido'));
                    }
                },
                error: function(xhr) {
                    console.error('Error al eliminar:', xhr);
                    alert('Error de conexión. Revisa la consola para más detalles.');
                }
            });
        }

        function verCalificaciones(id) {
            if (!id) {
                alert('ID de alumno no válido');
                return;
            }
            window.location.href = `boletas.php?id_alumno=${id}`;
        }

        function abrirModalMigracion() {
            document.getElementById('frmMigracion').reset();
            $('#migracion_grupo_origen').html('<option value="">Seleccionar</option>');
            $('#migracion_grupo_destino').html('<option value="">Seleccionar</option>');
            document.getElementById('modalMigracion').classList.add('active');
        }

        function migrarAlumnos() {
            const data = {
                action: 'migrar',
                id_carrera: $('#migracion_carrera').val(),
                grupo_origen: $('#migracion_grupo_origen').val(),
                grupo_destino: $('#migracion_grupo_destino').val()
            };

            if (!data.id_carrera || !data.grupo_origen || !data.grupo_destino) {
                alert('Todos los campos son obligatorios');
                return;
            }

            if (data.grupo_origen === data.grupo_destino) {
                alert('El grupo origen y destino deben ser diferentes');
                return;
            }

            if (!confirm('¿Estás seguro de migrar todos los alumnos del grupo origen al grupo destino?')) {
                return;
            }

            const btn = $('#btnMigrar');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Migrando...');

            $.ajax({
                url: '../../ajax/alumnos.php',
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Migración exitosa');
                        cerrarModal('modalMigracion');
                        cargarAlumnos();
                    } else {
                        alert('Error al migrar: ' + (response.message || 'Error desconocido'));
                    }
                    btn.prop('disabled', false).html('Migrar Alumnos');
                },
                error: function() {
                    alert('Error de conexión');
                    btn.prop('disabled', false).html('Migrar Alumnos');
                }
            });
        }

        function cerrarModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // Cerrar modales al hacer clic fuera
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
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