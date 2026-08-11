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
                    <button class="btn btn-success btn-sm" onclick="abrirModalAsignacion()">
                        <i class="fas fa-user-plus"></i> Asignar Maestro
                    </button>
                </div>
            </header-component>

            <!-- Tabs -->
            <div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
                <button class="btn btn-primary btn-sm tab-btn active" data-tab="materias">
                    <i class="fas fa-book"></i> Materias
                </button>
                <button class="btn btn-outline-primary btn-sm tab-btn" data-tab="asignaciones">
                    <i class="fas fa-users"></i> Asignaciones
                </button>
                <button class="btn btn-outline-primary btn-sm tab-btn" data-tab="grupos">
                    <i class="fas fa-layer-group"></i> Grupos
                </button>
            </div>

            <!-- Tab: Materias -->
            <div class="tab-content" id="tabMaterias">
                <table-component id="tablaMaterias"></table-component>
            </div>

            <!-- Tab: Asignaciones -->
            <div class="tab-content" id="tabAsignaciones" style="display:none;">
                <table-component id="tablaAsignaciones"></table-component>
            </div>

            <!-- Tab: Grupos -->
            <div class="tab-content" id="tabGrupos" style="display:none;">
                <table-component id="tablaGrupos"></table-component>
            </div>
        </div>
    </div>

    <!-- Modal: Materia -->
    <div class="modal" id="modalMateria" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:15px; max-width:500px; width:90%; max-height:90vh; overflow-y:auto; padding:30px; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 id="modalMateriaTitle">Nueva Materia</h3>
                <button onclick="cerrarModal('modalMateria')" style="background:none; border:none; font-size:24px; cursor:pointer; color:#7f8c8d;">&times;</button>
            </div>
            
            <form id="frmMateria">
                <input type="hidden" id="id_materia" name="id_materia">
                
                <div class="form-group">
                    <label class="form-label">Nombre de la Materia</label>
                    <input type="text" class="form-control" id="nombre_materia" name="nombre_materia" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Carrera</label>
                    <select class="form-control" id="id_carrera_materia" name="id_carrera" required></select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Semestre</label>
                    <select class="form-control" id="id_semestre" name="id_semestre" required>
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
                
                <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px; border-top:1px solid #edf2f7; padding-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal('modalMateria')">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarMateria">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Asignación -->
    <div class="modal" id="modalAsignacion" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:15px; max-width:500px; width:90%; max-height:90vh; overflow-y:auto; padding:30px; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3>Asignar Maestro a Materia</h3>
                <button onclick="cerrarModal('modalAsignacion')" style="background:none; border:none; font-size:24px; cursor:pointer; color:#7f8c8d;">&times;</button>
            </div>
            
            <form id="frmAsignacion">
                <div class="form-group">
                    <label class="form-label">Grupo</label>
                    <select class="form-control" id="id_grupounam" name="id_grupounam" required></select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Materia</label>
                    <select class="form-control" id="id_materia_asignacion" name="id_materia" required></select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Maestro</label>
                    <select class="form-control" id="id_usuario_maestro" name="id_usuario" required></select>
                </div>
                
                <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px; border-top:1px solid #edf2f7; padding-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal('modalAsignacion')">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnGuardarAsignacion">Asignar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let editandoMateria = false;

        $(document).ready(function() {
            // Tabs
            $('.tab-btn').click(function() {
                $('.tab-btn').removeClass('btn-primary').addClass('btn-outline-primary');
                $(this).removeClass('btn-outline-primary').addClass('btn-primary');
                
                const tab = $(this).data('tab');
                $('.tab-content').hide();
                $('#tab' + tab.charAt(0).toUpperCase() + tab.slice(1)).show();
            });

            // Configurar tablas
            configurarTablaMaterias();
            configurarTablaAsignaciones();
            configurarTablaGrupos();
            
            // Cargar datos
            cargarMaterias();
            cargarAsignaciones();
            cargarGrupos();
            cargarCarreras();
            cargarMaestros();
            
            // Eventos de guardado
            $('#btnGuardarMateria').click(guardarMateria);
            $('#btnGuardarAsignacion').click(guardarAsignacion);
        });

        function configurarTablaMaterias() {
            const tabla = document.getElementById('tablaMaterias');
            
            tabla.setColumns([
                { key: 'id_materia', label: 'ID' },
                { key: 'nombre_materia', label: 'Materia' },
                { key: 'nombre_carrera', label: 'Carrera' },
                { key: 'nombre_semestre', label: 'Semestre' }
            ]);
            
            tabla.setActions([
                { key: 'edit', icon: 'fa-edit', color: 'warning', label: 'Editar' },
                { key: 'delete', icon: 'fa-trash', color: 'danger', label: 'Eliminar' }
            ]);
            
            tabla.addEventListener('action', function(e) {
                if (e.detail.action === 'edit') {
                    editarMateria(e.detail.id);
                } else if (e.detail.action === 'delete') {
                    eliminarMateria(e.detail.id);
                }
            });
        }

        function configurarTablaAsignaciones() {
            const tabla = document.getElementById('tablaAsignaciones');
            
            tabla.setColumns([
                { key: 'id_asignacion', label: 'ID' },
                { key: 'nombre_grupo', label: 'Grupo' },
                { key: 'nombre_materia', label: 'Materia' },
                { key: 'nombre_maestro', label: 'Maestro' },
                { key: 'nombre_carrera', label: 'Carrera' }
            ]);
            
            tabla.setActions([
                { key: 'delete', icon: 'fa-trash', color: 'danger', label: 'Eliminar' }
            ]);
            
            tabla.addEventListener('action', function(e) {
                if (e.detail.action === 'delete') {
                    eliminarAsignacion(e.detail.id);
                }
            });
        }

        function configurarTablaGrupos() {
            const tabla = document.getElementById('tablaGrupos');
            
            tabla.setColumns([
                { key: 'id_grupounam', label: 'ID' },
                { key: 'nombre_grupo', label: 'Grupo' },
                { key: 'nombre_carrera', label: 'Carrera' },
                { key: 'total_alumnos', label: 'Alumnos' }
            ]);
            
            tabla.setActions([
                { key: 'edit', icon: 'fa-edit', color: 'warning', label: 'Editar' },
                { key: 'delete', icon: 'fa-trash', color: 'danger', label: 'Eliminar' }
            ]);
            
            tabla.addEventListener('action', function(e) {
                if (e.detail.action === 'edit') {
                    editarGrupo(e.detail.id);
                } else if (e.detail.action === 'delete') {
                    eliminarGrupo(e.detail.id);
                }
            });
        }

        function cargarMaterias() {
            const tabla = document.getElementById('tablaMaterias');
            $.ajax({
                url: '../../ajax/materias.php',
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                success: function(data) {
                    tabla.setData(data);
                }
            });
        }

        function cargarAsignaciones() {
            const tabla = document.getElementById('tablaAsignaciones');
            $.ajax({
                url: '../../ajax/asignaciones.php',
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                success: function(data) {
                    tabla.setData(data);
                }
            });
        }

        function cargarGrupos() {
            const tabla = document.getElementById('tablaGrupos');
            $.ajax({
                url: '../../ajax/grupos.php',
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                success: function(data) {
                    tabla.setData(data);
                }
            });
        }

        function cargarCarreras() {
            $.ajax({
                url: '../../ajax/carreras.php',
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                success: function(data) {
                    let html = '<option value="">Seleccionar carrera</option>';
                    data.forEach(function(c) {
                        html += `<option value="${c.id_carrera}">${c.nombre_carrera}</option>`;
                    });
                    $('#id_carrera_materia').html(html);
                    $('#id_carrera_grupo').html(html);
                }
            });
        }

        function cargarMaestros() {
            $.ajax({
                url: '../../ajax/users.php',
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                success: function(data) {
                    let html = '<option value="">Seleccionar maestro</option>';
                    data.filter(u => u.id_rol == 3).forEach(function(u) {
                        html += `<option value="${u.id_usuario}">${u.nombre} ${u.apellido_paterno}</option>`;
                    });
                    $('#id_usuario_maestro').html(html);
                }
            });
        }

        function abrirModalMateria() {
            editandoMateria = false;
            document.getElementById('modalMateriaTitle').textContent = 'Nueva Materia';
            document.getElementById('frmMateria').reset();
            document.getElementById('id_materia').value = '';
            document.getElementById('modalMateria').style.display = 'flex';
        }

        function editarMateria(id) {
            editandoMateria = true;
            document.getElementById('modalMateriaTitle').textContent = 'Editar Materia';
            
            $.ajax({
                url: '../../ajax/materias.php',
                type: 'POST',
                data: { action: 'get', id: id },
                dataType: 'json',
                success: function(data) {
                    $('#id_materia').val(data.id_materia);
                    $('#nombre_materia').val(data.nombre_materia);
                    $('#id_carrera_materia').val(data.id_carrera);
                    $('#id_semestre').val(data.id_semestre);
                    document.getElementById('modalMateria').style.display = 'flex';
                }
            });
        }

        function guardarMateria() {
            const data = {
                action: editandoMateria ? 'update' : 'create',
                id: $('#id_materia').val(),
                nombre_materia: $('#nombre_materia').val(),
                id_carrera: $('#id_carrera_materia').val(),
                id_semestre: $('#id_semestre').val()
            };

            if (!data.nombre_materia || !data.id_carrera) {
                alert('Todos los campos son obligatorios');
                return;
            }

            $.ajax({
                url: '../../ajax/materias.php',
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(editandoMateria ? 'Materia actualizada' : 'Materia creada');
                        cerrarModal('modalMateria');
                        cargarMaterias();
                    } else {
                        alert('Error al guardar');
                    }
                }
            });
        }

        function eliminarMateria(id) {
            if (confirm('¿Eliminar esta materia?')) {
                $.ajax({
                    url: '../../ajax/materias.php',
                    type: 'POST',
                    data: { action: 'delete', id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Materia eliminada');
                            cargarMaterias();
                        }
                    }
                });
            }
        }

        function abrirModalAsignacion() {
            document.getElementById('frmAsignacion')[0].reset();
            document.getElementById('modalAsignacion').style.display = 'flex';
            cargarSelectsAsignacion();
        }

        function cargarSelectsAsignacion() {
            // Cargar grupos
            $.ajax({
                url: '../../ajax/grupos.php',
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                success: function(data) {
                    let html = '<option value="">Seleccionar grupo</option>';
                    data.forEach(function(g) {
                        html += `<option value="${g.id_grupounam}">${g.nombre_grupo}</option>`;
                    });
                    $('#id_grupounam').html(html);
                }
            });

            // Cargar materias
            $.ajax({
                url: '../../ajax/materias.php',
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                success: function(data) {
                    let html = '<option value="">Seleccionar materia</option>';
                    data.forEach(function(m) {
                        html += `<option value="${m.id_materia}">${m.nombre_materia}</option>`;
                    });
                    $('#id_materia_asignacion').html(html);
                }
            });
        }

        function guardarAsignacion() {
            const data = {
                action: 'create',
                id_grupounam: $('#id_grupounam').val(),
                id_materia: $('#id_materia_asignacion').val(),
                id_usuario: $('#id_usuario_maestro').val()
            };

            if (!data.id_grupounam || !data.id_materia || !data.id_usuario) {
                alert('Todos los campos son obligatorios');
                return;
            }

            $.ajax({
                url: '../../ajax/asignaciones.php',
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Asignación creada');
                        cerrarModal('modalAsignacion');
                        cargarAsignaciones();
                    } else {
                        alert('Error al asignar');
                    }
                }
            });
        }

        function eliminarAsignacion(id) {
            if (confirm('¿Eliminar esta asignación?')) {
                $.ajax({
                    url: '../../ajax/asignaciones.php',
                    type: 'POST',
                    data: { action: 'delete', id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Asignación eliminada');
                            cargarAsignaciones();
                        }
                    }
                });
            }
        }

        function cerrarModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        // Cerrar modales al hacer clic fuera
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
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