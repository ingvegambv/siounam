<?php
// admin/pages/asignar_materias.php
require_once '../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Materias - SIOUNAM</title>
    <link rel="stylesheet" href="../../assets/css/components.css">
    <script src="../../assets/components/sidebar-component.js" defer></script>
    <script src="../../assets/components/header-component.js" defer></script>
    <script src="../../assets/components/table-component.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="app-layout">
        <sidebar-component base-path="../../"></sidebar-component>

        <div class="main-content" id="mainContent">
            <header-component title="Asignar Materias" icon="chalkboard-teacher">
                <div slot="actions">
                    <button class="btn btn-primary btn-sm" onclick="abrirModalMateria()">
                        <i class="fas fa-plus"></i> Nueva Materia
                    </button>
                    <button class="btn btn-success btn-sm" onclick="abrirModalGrupo()">
                        <i class="fas fa-layer-group"></i> Nuevo Grupo
                    </button>
                    <button class="btn btn-warning btn-sm" onclick="abrirModalAsignacion()">
                        <i class="fas fa-user-plus"></i> Asignar Maestro
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="desasignarTodas()">
                        <i class="fas fa-times-circle"></i> Desasignar Todas
                    </button>
                </div>
            </header-component>

            <!-- Tabs -->
            <div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
                <button class="btn btn-primary btn-sm tab-btn active" data-tab="materias">
                    <i class="fas fa-book"></i> Materias
                </button>
                <button class="btn btn-outline-primary btn-sm tab-btn" data-tab="grupos">
                    <i class="fas fa-layer-group"></i> Grupos
                </button>
                <button class="btn btn-outline-primary btn-sm tab-btn" data-tab="asignaciones">
                    <i class="fas fa-users"></i> Asignaciones
                </button>
            </div>

            <!-- Tab: Materias -->
            <div class="tab-content" id="tabMaterias">
                <table-component id="tablaMaterias"></table-component>
            </div>

            <!-- Tab: Grupos -->
            <div class="tab-content" id="tabGrupos" style="display:none;">
                <table-component id="tablaGrupos"></table-component>
            </div>

            <!-- Tab: Asignaciones -->
            <div class="tab-content" id="tabAsignaciones" style="display:none;">
                <table-component id="tablaAsignaciones"></table-component>
            </div>
        </div>
    </div>

    <!-- ===== MODAL MATERIA ===== -->
    <div class="modal fade" id="modalMateria" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMateriaTitle">Nueva Materia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="frmMateria">
                        <input type="hidden" id="id_materia" name="id_materia">
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Nombre de la Materia *</label>
                            <input type="text" class="form-control" id="nombre_materia" name="nombre_materia" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Carrera *</label>
                            <select class="form-control" id="id_carrera_materia" name="id_carrera" required>
                                <option value="">Seleccionar carrera</option>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Semestre *</label>
                            <select class="form-control" id="id_semestre" name="id_semestre" required>
                                <option value="">Seleccionar semestre</option>
                                <option value="1">1er Semestre</option>
                                <option value="2">2do Semestre</option>
                                <option value="3">3er Semestre</option>
                                <option value="4">4to Semestre</option>
                                <option value="5">5to Semestre</option>
                                <option value="6">6to Semestre</option>
                                <option value="7">7mo Semestre</option>
                                <option value="8">8vo Semestre</option>
                                <option value="9">9no Semestre</option>
                                <option value="10">10mo Semestre</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarMateria">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MODAL GRUPO ===== -->
    <div class="modal fade" id="modalGrupo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalGrupoTitle">Nuevo Grupo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="frmGrupo">
                        <input type="hidden" id="id_grupo" name="id_grupo">
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Nombre del Grupo *</label>
                            <input type="text" class="form-control" id="nombre_grupo" name="nombre_grupo" required placeholder="Ej: 1er Semestre A">
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Carrera *</label>
                            <select class="form-control" id="id_carrera_grupo" name="id_carrera" required>
                                <option value="">Seleccionar carrera</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarGrupo">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MODAL ASIGNACION ===== -->
    <div class="modal fade" id="modalAsignacion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Asignar Maestro a Materia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="frmAsignacion">
                        <div class="form-group mb-3">
                            <label class="form-label">Grupo *</label>
                            <select class="form-control" id="id_grupounam" name="id_grupounam" required>
                                <option value="">Seleccionar grupo</option>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Materia *</label>
                            <select class="form-control" id="id_materia_asignacion" name="id_materia" required>
                                <option value="">Seleccionar materia</option>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Maestro *</label>
                            <select class="form-control" id="id_usuario_maestro" name="id_usuario" required>
                                <option value="">Seleccionar maestro</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnGuardarAsignacion">Asignar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ===== CONFIGURACIÓN DE RUTAS =====
        const BASE_URL = '../../ajax/';
        const URL_MATERIAS = BASE_URL + 'materias.php';
        const URL_GRUPOS = BASE_URL + 'grupos.php';
        const URL_ASIGNACIONES = BASE_URL + 'asignaciones.php';
        const URL_CARRERAS = BASE_URL + 'carreras.php';
        const URL_USERS = BASE_URL + 'users.php';

        // Variables globales
        let modalMateriaInstance = null;
        let modalGrupoInstance = null;
        let modalAsignacionInstance = null;
        let editandoMateria = false;
        let editandoGrupo = false;

        // Variables para las tablas
        let tablaMaterias = null;
        let tablaGrupos = null;
        let tablaAsignaciones = null;

        $(document).ready(function() {
            console.log('🚀 Página de Asignar Materias cargada');

            // Inicializar modales
            modalMateriaInstance = new bootstrap.Modal(document.getElementById('modalMateria'));
            modalGrupoInstance = new bootstrap.Modal(document.getElementById('modalGrupo'));
            modalAsignacionInstance = new bootstrap.Modal(document.getElementById('modalAsignacion'));

            // Tabs
            $('.tab-btn').click(function() {
                $('.tab-btn').removeClass('btn-primary').addClass('btn-outline-primary');
                $(this).removeClass('btn-outline-primary').addClass('btn-primary');
                
                const tab = $(this).data('tab');
                $('.tab-content').hide();
                $('#tab' + tab.charAt(0).toUpperCase() + tab.slice(1)).show();
            });

            // Configurar tablas
            configurarTablas();
            
            // Cargar datos
            cargarMaterias();
            cargarGrupos();
            cargarAsignaciones();
            cargarCarreras();
            cargarMaestros();

            // Eventos de guardado
            $('#btnGuardarMateria').click(guardarMateria);
            $('#btnGuardarGrupo').click(guardarGrupo);
            $('#btnGuardarAsignacion').click(guardarAsignacion);

            // Resetear formularios al cerrar modales
            document.getElementById('modalMateria').addEventListener('hidden.bs.modal', function() {
                document.getElementById('frmMateria').reset();
                document.getElementById('id_materia').value = '';
                editandoMateria = false;
            });
            
            document.getElementById('modalGrupo').addEventListener('hidden.bs.modal', function() {
                document.getElementById('frmGrupo').reset();
                document.getElementById('id_grupo').value = '';
                editandoGrupo = false;
            });
        });

        // ===== CONFIGURAR TABLAS =====
        function configurarTablas() {
            // Tabla Materias
            tablaMaterias = document.getElementById('tablaMaterias');
            tablaMaterias.setColumns([
                { key: 'id_materia', label: 'ID' },
                { key: 'nombre_materia', label: 'Materia' },
                { key: 'nombre_carrera', label: 'Carrera' },
                { key: 'nombre_semestre', label: 'Semestre' }
            ]);
            tablaMaterias.setActions([
                { key: 'edit_materia', icon: 'fa-edit', color: 'warning', label: 'Editar' },
                { key: 'delete_materia', icon: 'fa-trash', color: 'danger', label: 'Eliminar' }
            ]);
            tablaMaterias.setIdField('id_materia');

            // Tabla Grupos
            tablaGrupos = document.getElementById('tablaGrupos');
            tablaGrupos.setColumns([
                { key: 'id_grupounam', label: 'ID' },
                { key: 'nombre_grupo', label: 'Grupo' },
                { key: 'nombre_carrera', label: 'Carrera' },
                { key: 'total_alumnos', label: 'Alumnos' }
            ]);
            tablaGrupos.setActions([
                { key: 'edit_grupo', icon: 'fa-edit', color: 'warning', label: 'Editar' },
                { key: 'delete_grupo', icon: 'fa-trash', color: 'danger', label: 'Eliminar' }
            ]);
            tablaGrupos.setIdField('id_grupounam');

            // Tabla Asignaciones
            tablaAsignaciones = document.getElementById('tablaAsignaciones');
            tablaAsignaciones.setColumns([
                { key: 'id_asignacion', label: 'ID' },
                { key: 'nombre_grupo', label: 'Grupo' },
                { key: 'nombre_materia', label: 'Materia' },
                { key: 'nombre_maestro', label: 'Maestro' },
                { key: 'nombre_carrera', label: 'Carrera' },
                { key: 'nombre_semestre', label: 'Semestre' }
            ]);
            tablaAsignaciones.setActions([
                { key: 'delete_asignacion', icon: 'fa-trash', color: 'danger', label: 'Eliminar' },
                { key: 'desasignar', icon: 'fa-times-circle', color: 'danger', label: 'Desasignar' }
            ]);
            tablaAsignaciones.setIdField('id_asignacion');

            // ===== EVENTOS DE ACCIÓN =====
            // Evento para Materias
            tablaMaterias.addEventListener('action', function(e) {
                console.log('📌 Evento Materias:', e.detail);
                const action = e.detail.action;
                const id = e.detail.id;
                const data = e.detail.data;

                if (action === 'edit_materia') {
                    editarMateria(id);
                } else if (action === 'delete_materia') {
                    eliminarMateria(id);
                }
            });

            // Evento para Grupos
            tablaGrupos.addEventListener('action', function(e) {
                console.log('📌 Evento Grupos:', e.detail);
                const action = e.detail.action;
                const id = e.detail.id;
                const data = e.detail.data;

                if (action === 'edit_grupo') {
                    editarGrupo(id);
                } else if (action === 'delete_grupo') {
                    eliminarGrupo(id);
                }
            });

            // Evento para Asignaciones
            tablaAsignaciones.addEventListener('action', function(e) {
                console.log('📌 Evento Asignaciones:', e.detail);
                const action = e.detail.action;
                const id = e.detail.id;
                const data = e.detail.data;

                if (action === 'delete_asignacion') {
                    eliminarAsignacion(id);
                } else if (action === 'desasignar') {
                    desasignar(id);
                }
            });
        }

        // ===== CARGAR DATOS =====
        function cargarMaterias() {
            console.log('🔄 Cargando materias...');
            $.ajax({
                url: URL_MATERIAS,
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                success: function(data) {
                    console.log('✅ Materias cargadas:', data.length);
                    tablaMaterias.setData(data);
                },
                error: function(xhr) {
                    console.error('❌ Error al cargar materias:', xhr.responseText);
                }
            });
        }

        function cargarGrupos() {
            console.log('🔄 Cargando grupos...');
            $.ajax({
                url: URL_GRUPOS,
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                success: function(data) {
                    console.log('✅ Grupos cargados:', data.length);
                    tablaGrupos.setData(data);
                },
                error: function(xhr) {
                    console.error('❌ Error al cargar grupos:', xhr.responseText);
                }
            });
        }

        function cargarAsignaciones() {
            console.log('🔄 Cargando asignaciones...');
            $.ajax({
                url: URL_ASIGNACIONES,
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                success: function(data) {
                    console.log('✅ Asignaciones cargadas:', data.length);
                    tablaAsignaciones.setData(data);
                },
                error: function(xhr) {
                    console.error('❌ Error al cargar asignaciones:', xhr.responseText);
                }
            });
        }

        function cargarCarreras() {
            console.log('🔄 Cargando carreras...');
            $.ajax({
                url: URL_CARRERAS,
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                success: function(data) {
                    console.log('✅ Carreras cargadas:', data.length);
                    let html = '<option value="">Seleccionar carrera</option>';
                    data.forEach(function(c) {
                        html += `<option value="${c.id_carrera}">${c.nombre_carrera}</option>`;
                    });
                    $('#id_carrera_materia').html(html);
                    $('#id_carrera_grupo').html(html);
                },
                error: function(xhr) {
                    console.error('❌ Error al cargar carreras:', xhr.responseText);
                }
            });
        }

        function cargarMaestros() {
            console.log('🔄 Cargando maestros...');
            
            // Cargar maestros
            $.ajax({
                url: URL_USERS,
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                success: function(data) {
                    const maestros = data.filter(u => u.id_rol == 3);
                    console.log('👨‍🏫 Maestros encontrados:', maestros.length);
                    
                    let html = '<option value="">Seleccionar maestro</option>';
                    maestros.forEach(function(u) {
                        html += `<option value="${u.id_usuario}">${u.nombre} ${u.apellido_paterno}</option>`;
                    });
                    $('#id_usuario_maestro').html(html);
                },
                error: function(xhr) {
                    console.error('❌ Error al cargar maestros:', xhr.responseText);
                }
            });

            // Cargar grupos para asignación
            $.ajax({
                url: URL_GRUPOS,
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                success: function(data) {
                    let html = '<option value="">Seleccionar grupo</option>';
                    data.forEach(function(g) {
                        html += `<option value="${g.id_grupounam}">${g.nombre_grupo}</option>`;
                    });
                    $('#id_grupounam').html(html);
                },
                error: function(xhr) {
                    console.error('❌ Error al cargar grupos:', xhr.responseText);
                }
            });

            // Cargar materias para asignación
            $.ajax({
                url: URL_MATERIAS,
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                success: function(data) {
                    let html = '<option value="">Seleccionar materia</option>';
                    data.forEach(function(m) {
                        html += `<option value="${m.id_materia}">${m.nombre_materia}</option>`;
                    });
                    $('#id_materia_asignacion').html(html);
                },
                error: function(xhr) {
                    console.error('❌ Error al cargar materias:', xhr.responseText);
                }
            });
        }

        // ===== FUNCIONES MATERIA =====
        function abrirModalMateria() {
            editandoMateria = false;
            document.getElementById('modalMateriaTitle').textContent = 'Nueva Materia';
            document.getElementById('frmMateria').reset();
            document.getElementById('id_materia').value = '';
            modalMateriaInstance.show();
        }

        function editarMateria(id) {
            console.log('✏️ Editando materia ID:', id);
            editandoMateria = true;
            document.getElementById('modalMateriaTitle').textContent = 'Editar Materia';
            
            $.ajax({
                url: URL_MATERIAS,
                type: 'POST',
                data: { action: 'get', id: id },
                dataType: 'json',
                success: function(data) {
                    console.log('📝 Datos de materia:', data);
                    if (data) {
                        $('#id_materia').val(data.id_materia);
                        $('#nombre_materia').val(data.nombre_materia);
                        $('#id_carrera_materia').val(data.id_carrera);
                        $('#id_semestre').val(data.id_semestre);
                        modalMateriaInstance.show();
                    } else {
                        alert('No se encontraron datos de la materia');
                    }
                },
                error: function(xhr) {
                    console.error('❌ Error al obtener materia:', xhr.responseText);
                    alert('Error al cargar los datos de la materia');
                }
            });
        }

        function guardarMateria() {
            const data = {
                action: editandoMateria ? 'update' : 'create',
                id: $('#id_materia').val(),
                nombre_materia: $('#nombre_materia').val().trim(),
                id_carrera: $('#id_carrera_materia').val(),
                id_semestre: $('#id_semestre').val()
            };

            if (!data.nombre_materia || !data.id_carrera || !data.id_semestre) {
                alert('Todos los campos son obligatorios');
                return;
            }

            const btn = $('#btnGuardarMateria');
            const originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> Guardando...').prop('disabled', true);

            $.ajax({
                url: URL_MATERIAS,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    console.log('✅ Respuesta:', response);
                    if (response.success) {
                        alert(editandoMateria ? '✅ Materia actualizada' : '✅ Materia creada');
                        modalMateriaInstance.hide();
                        cargarMaterias();
                        cargarMaestros();
                    } else {
                        alert('❌ Error: ' + (response.message || 'Error desconocido'));
                    }
                },
                error: function(xhr) {
                    console.error('❌ Error:', xhr.responseText);
                    alert('❌ Error al conectar con el servidor');
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                }
            });
        }

        function eliminarMateria(id) {
            console.log('🗑️ Eliminando materia ID:', id);
            if (!confirm('¿Estás seguro de eliminar esta materia?')) {
                return;
            }
            
            $.ajax({
                url: URL_MATERIAS,
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    console.log('✅ Respuesta:', response);
                    if (response.success) {
                        alert('✅ Materia eliminada');
                        cargarMaterias();
                        cargarMaestros();
                        cargarAsignaciones();
                    } else {
                        alert('❌ Error: ' + (response.message || 'Error desconocido'));
                    }
                },
                error: function(xhr) {
                    console.error('❌ Error:', xhr.responseText);
                    alert('❌ Error al conectar con el servidor');
                }
            });
        }

        // ===== FUNCIONES GRUPO =====
        function abrirModalGrupo() {
            editandoGrupo = false;
            document.getElementById('modalGrupoTitle').textContent = 'Nuevo Grupo';
            document.getElementById('frmGrupo').reset();
            document.getElementById('id_grupo').value = '';
            modalGrupoInstance.show();
        }

        function editarGrupo(id) {
            console.log('✏️ Editando grupo ID:', id);
            editandoGrupo = true;
            document.getElementById('modalGrupoTitle').textContent = 'Editar Grupo';
            
            $.ajax({
                url: URL_GRUPOS,
                type: 'POST',
                data: { action: 'get', id: id },
                dataType: 'json',
                success: function(data) {
                    console.log('📝 Datos de grupo:', data);
                    if (data) {
                        $('#id_grupo').val(data.id_grupounam);
                        $('#nombre_grupo').val(data.nombre_grupo);
                        $('#id_carrera_grupo').val(data.id_carrera);
                        modalGrupoInstance.show();
                    } else {
                        alert('No se encontraron datos del grupo');
                    }
                },
                error: function(xhr) {
                    console.error('❌ Error al obtener grupo:', xhr.responseText);
                    alert('Error al cargar los datos del grupo');
                }
            });
        }

        function guardarGrupo() {
            const data = {
                action: editandoGrupo ? 'update' : 'create',
                id: $('#id_grupo').val(),
                nombre_grupo: $('#nombre_grupo').val().trim(),
                id_carrera: $('#id_carrera_grupo').val()
            };

            if (!data.nombre_grupo || !data.id_carrera) {
                alert('Todos los campos son obligatorios');
                return;
            }

            const btn = $('#btnGuardarGrupo');
            const originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> Guardando...').prop('disabled', true);

            $.ajax({
                url: URL_GRUPOS,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    console.log('✅ Respuesta:', response);
                    if (response.success) {
                        alert(editandoGrupo ? '✅ Grupo actualizado' : '✅ Grupo creado');
                        modalGrupoInstance.hide();
                        cargarGrupos();
                        cargarMaestros();
                    } else {
                        alert('❌ Error: ' + (response.message || 'Error desconocido'));
                    }
                },
                error: function(xhr) {
                    console.error('❌ Error:', xhr.responseText);
                    alert('❌ Error al conectar con el servidor');
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                }
            });
        }

        function eliminarGrupo(id) {
            console.log('🗑️ Eliminando grupo ID:', id);
            if (!confirm('¿Estás seguro de eliminar este grupo?')) {
                return;
            }
            
            $.ajax({
                url: URL_GRUPOS,
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    console.log('✅ Respuesta:', response);
                    if (response.success) {
                        alert('✅ Grupo eliminado');
                        cargarGrupos();
                        cargarMaestros();
                        cargarAsignaciones();
                    } else {
                        alert('❌ Error: ' + (response.message || 'Error desconocido'));
                    }
                },
                error: function(xhr) {
                    console.error('❌ Error:', xhr.responseText);
                    alert('❌ Error al conectar con el servidor');
                }
            });
        }

        // ===== FUNCIONES ASIGNACION =====
        function abrirModalAsignacion() {
            document.getElementById('frmAsignacion').reset();
            modalAsignacionInstance.show();
        }

        function guardarAsignacion() {
            const idGrupo = $('#id_grupounam').val();
            const idMateria = $('#id_materia_asignacion').val();
            const idUsuario = $('#id_usuario_maestro').val();

            if (!idGrupo || !idMateria || !idUsuario) {
                alert('Todos los campos son obligatorios');
                return;
            }

            const data = {
                action: 'create',
                id_grupounam: idGrupo,
                id_materia: idMateria,
                id_usuario: idUsuario
            };

            const btn = $('#btnGuardarAsignacion');
            const originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> Asignando...').prop('disabled', true);

            $.ajax({
                url: URL_ASIGNACIONES,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    console.log('✅ Respuesta:', response);
                    if (response.success) {
                        alert('✅ Asignación creada correctamente');
                        modalAsignacionInstance.hide();
                        cargarAsignaciones();
                    } else {
                        alert('❌ Error: ' + (response.message || 'Error desconocido'));
                    }
                },
                error: function(xhr) {
                    console.error('❌ Error:', xhr.responseText);
                    alert('❌ Error al conectar con el servidor');
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                }
            });
        }

        function eliminarAsignacion(id) {
            console.log('🗑️ Eliminando asignación ID:', id);
            if (!confirm('¿Estás seguro de eliminar esta asignación?')) {
                return;
            }
            
            $.ajax({
                url: URL_ASIGNACIONES,
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    console.log('✅ Respuesta:', response);
                    if (response.success) {
                        alert('✅ Asignación eliminada');
                        cargarAsignaciones();
                    } else {
                        alert('❌ Error: ' + (response.message || 'Error desconocido'));
                    }
                },
                error: function(xhr) {
                    console.error('❌ Error:', xhr.responseText);
                    alert('❌ Error al conectar con el servidor');
                }
            });
        }

        function desasignar(id) {
            console.log('🔄 Desasignando ID:', id);
            if (!confirm('¿Estás seguro de desasignar este maestro?')) {
                return;
            }
            
            $.ajax({
                url: URL_ASIGNACIONES,
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    console.log('✅ Respuesta:', response);
                    if (response.success) {
                        alert('✅ Maestro desasignado correctamente');
                        cargarAsignaciones();
                    } else {
                        alert('❌ Error: ' + (response.message || 'Error desconocido'));
                    }
                },
                error: function(xhr) {
                    console.error('❌ Error:', xhr.responseText);
                    alert('❌ Error al conectar con el servidor');
                }
            });
        }

        function desasignarTodas() {
            console.log('🔄 Desasignando TODAS las asignaciones');
            if (!confirm('⚠️ ¿Estás seguro de DESASIGNAR TODOS los maestros?')) {
                return;
            }
            
            if (!confirm('⚠️ Confirmación final: ¿Realmente deseas eliminar TODAS las asignaciones?')) {
                return;
            }
            
            $.ajax({
                url: URL_ASIGNACIONES,
                type: 'POST',
                data: { action: 'delete_all' },
                dataType: 'json',
                success: function(response) {
                    console.log('✅ Respuesta:', response);
                    if (response.success) {
                        alert('✅ Todas las asignaciones han sido eliminadas');
                        cargarAsignaciones();
                    } else {
                        alert('❌ Error: ' + (response.message || 'Error desconocido'));
                    }
                },
                error: function(xhr) {
                    console.error('❌ Error:', xhr.responseText);
                    alert('❌ Error al conectar con el servidor');
                }
            });
        }

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