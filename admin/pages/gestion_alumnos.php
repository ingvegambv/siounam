<?php
// admin/pages/gestion_alumnos.php
require_once '../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Alumnos - SIOUNAM</title>
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

            <!-- Filtros -->
            <div style="background:#fff; padding:15px 20px; border-radius:15px; margin-bottom:20px; box-shadow:0 2px 15px rgba(0,0,0,0.05);">
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:15px;">
                    <div class="form-group">
                        <label class="form-label">Carrera</label>
                        <select class="form-control" id="filtroCarreraAlumno" onchange="filtrarAlumnos()">
                            <option value="">Todas</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Grupo</label>
                        <select class="form-control" id="filtroGrupoAlumno" onchange="filtrarAlumnos()">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="buscarAlumno" placeholder="Nombre o matrícula..." onkeyup="filtrarAlumnos()">
                    </div>
                </div>
            </div>

            <!-- Tabla de alumnos -->
            <table-component id="tablaAlumnos"></table-component>
        </div>
    </div>

    <!-- Modal: Alumno -->
    <div class="modal" id="modalAlumno" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:15px; max-width:550px; width:90%; max-height:90vh; overflow-y:auto; padding:30px; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 id="modalAlumnoTitle">Nuevo Alumno</h3>
                <button onclick="cerrarModal('modalAlumno')" style="background:none; border:none; font-size:24px; cursor:pointer; color:#7f8c8d;">&times;</button>
            </div>
            
            <form id="frmAlumno">
                <input type="hidden" id="id_alumno" name="id_alumno">
                
                <div class="form-group">
                    <label class="form-label">Carrera</label>
                    <select class="form-control" id="id_carrera_alumno" name="id_carrera" required></select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Grupo</label>
                    <select class="form-control" id="id_grupounam_alumno" name="id_grupounam" required></select>
                </div>
                
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre_alumno" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellido Paterno</label>
                        <input type="text" class="form-control" id="apellido_paterno_alumno" name="apellido_paterno" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellido Materno</label>
                        <input type="text" class="form-control" id="apellido_materno_alumno" name="apellido_materno" required>
                    </div>
                </div>
                
                <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px; border-top:1px solid #edf2f7; padding-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal('modalAlumno')">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarAlumno">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Migración -->
    <div class="modal" id="modalMigracion" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:15px; max-width:500px; width:90%; max-height:90vh; overflow-y:auto; padding:30px; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3>Migrar Alumnos de Semestre</h3>
                <button onclick="cerrarModal('modalMigracion')" style="background:none; border:none; font-size:24px; cursor:pointer; color:#7f8c8d;">&times;</button>
            </div>
            
            <form id="frmMigracion">
                <div class="form-group">
                    <label class="form-label">Carrera</label>
                    <select class="form-control" id="migracion_carrera" required></select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Grupo Origen</label>
                    <select class="form-control" id="migracion_grupo_origen" required></select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Grupo Destino</label>
                    <select class="form-control" id="migracion_grupo_destino" required></select>
                </div>
                
                <div style="background:#fff3cd; padding:15px; border-radius:10px; margin:15px 0;">
                    <p style="margin:0; font-size:13px; color:#856404;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Esta acción moverá todos los alumnos del grupo origen al grupo destino.
                        Los alumnos conservarán sus calificaciones.
                    </p>
                </div>
                
                <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px; border-top:1px solid #edf2f7; padding-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal('modalMigracion')">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="btnMigrar">Migrar Alumnos</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let editandoAlumno = false;
        let todosLosAlumnos = [];

        $(document).ready(function() {
            const tabla = document.getElementById('tablaAlumnos');
            
            tabla.setColumns([
                { key: 'id_alumno', label: 'ID' },
                { key: 'nombre_completo', label: 'Nombre' },
                { key: 'nombre_carrera', label: 'Carrera' },
                { key: 'nombre_grupo', label: 'Grupo' }
            ]);
            
            tabla.setActions([
                { key: 'edit', icon: 'fa-edit', color: 'warning', label: 'Editar' },
                { key: 'delete', icon: 'fa-trash', color: 'danger', label: 'Eliminar' },
                { key: 'calificaciones', icon: 'fa-star', color: 'info', label: 'Ver Calificaciones' }
            ]);
            
            tabla.addEventListener('action', function(e) {
                if (e.detail.action === 'edit') {
                    editarAlumno(e.detail.id);
                } else if (e.detail.action === 'delete') {
                    eliminarAlumno(e.detail.id);
                } else if (e.detail.action === 'calificaciones') {
                    verCalificaciones(e.detail.id);
                }
            });

            cargarCarreras();
            cargarGrupos();
            cargarAlumnos();

            // Evento cambio de carrera en filtros
            $('#filtroCarreraAlumno').change(function() {
                cargarGruposFiltro($(this).val());
            });

            // Guardar alumno
            $('#btnGuardarAlumno').click(guardarAlumno);
            
            // Migrar alumnos
            $('#btnMigrar').click(migrarAlumnos);
        });

        function cargarCarreras() {
            $.ajax({
                url: '../../ajax/carreras.php',
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                success: function(data) {
                    let html = '<option value="">Seleccionar carrera</option>';
                    let htmlFiltro = '<option value="">Todas</option>';
                    data.forEach(function(c) {
                        html += `<option value="${c.id_carrera}">${c.nombre_carrera}</option>`;
                        htmlFiltro += `<option value="${c.id_carrera}">${c.nombre_carrera}</option>`;
                    });
                    $('#id_carrera_alumno').html(html);
                    $('#migracion_carrera').html(html);
                    $('#filtroCarreraAlumno').html(htmlFiltro);
                }
            });
        }

        function cargarGrupos() {
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
                    $('#id_grupounam_alumno').html(html);
                }
            });
        }

        function cargarGruposFiltro(idCarrera) {
            $.ajax({
                url: '../../ajax/grupos.php',
                type: 'POST',
                data: { action: 'list', id_carrera: idCarrera },
                dataType: 'json',
                success: function(data) {
                    let html = '<option value="">Todos</option>';
                    data.forEach(function(g) {
                        html += `<option value="${g.id_grupounam}">${g.nombre_grupo}</option>`;
                    });
                    $('#filtroGrupoAlumno').html(html);
                    
                    // También para migración
                    let html2 = '<option value="">Seleccionar grupo</option>';
                    data.forEach(function(g) {
                        html2 += `<option value="${g.id_grupounam}">${g.nombre_grupo}</option>`;
                    });
                    $('#migracion_grupo_origen').html(html2);
                    $('#migracion_grupo_destino').html(html2);
                }
            });
        }

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
                }
            });
        }

        function filtrarAlumnos() {
            const idCarrera = $('#filtroCarreraAlumno').val();
            const idGrupo = $('#filtroGrupoAlumno').val();
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
                    a.nombre.toLowerCase().includes(busqueda) ||
                    a.apellido_paterno.toLowerCase().includes(busqueda) ||
                    a.id_alumno.toString().includes(busqueda)
                );
            }
            
            const tabla = document.getElementById('tablaAlumnos');
            tabla.setData(filtered);
        }

        function abrirModalAlumno() {
            editandoAlumno = false;
            document.getElementById('modalAlumnoTitle').textContent = 'Nuevo Alumno';
            document.getElementById('frmAlumno').reset();
            document.getElementById('id_alumno').value = '';
            document.getElementById('modalAlumno').style.display = 'flex';
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
                    $('#id_carrera_alumno').val(data.id_carrera);
                    $('#id_grupounam_alumno').val(data.id_grupounam);
                    $('#nombre_alumno').val(data.nombre);
                    $('#apellido_paterno_alumno').val(data.apellido_paterno);
                    $('#apellido_materno_alumno').val(data.apellido_materno);
                    document.getElementById('modalAlumno').style.display = 'flex';
                }
            });
        }

        function guardarAlumno() {
            const data = {
                action: editandoAlumno ? 'update' : 'create',
                id: $('#id_alumno').val(),
                id_carrera: $('#id_carrera_alumno').val(),
                id_grupounam: $('#id_grupounam_alumno').val(),
                nombre: $('#nombre_alumno').val(),
                apellido_paterno: $('#apellido_paterno_alumno').val(),
                apellido_materno: $('#apellido_materno_alumno').val()
            };

            if (!data.id_carrera || !data.id_grupounam || !data.nombre) {
                alert('Todos los campos son obligatorios');
                return;
            }

            $.ajax({
                url: '../../ajax/alumnos.php',
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(editandoAlumno ? 'Alumno actualizado' : 'Alumno creado');
                        cerrarModal('modalAlumno');
                        cargarAlumnos();
                    } else {
                        alert('Error al guardar');
                    }
                }
            });
        }

        function eliminarAlumno(id) {
            if (confirm('¿Eliminar este alumno?')) {
                $.ajax({
                    url: '../../ajax/alumnos.php',
                    type: 'POST',
                    data: { action: 'delete', id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Alumno eliminado');
                            cargarAlumnos();
                        }
                    }
                });
            }
        }

        function verCalificaciones(id) {
            alert('Ver calificaciones del alumno ID: ' + id);
            // Aquí se abriría un modal con las calificaciones del alumno
        }

        function abrirModalMigracion() {
            document.getElementById('frmMigracion').reset();
            document.getElementById('modalMigracion').style.display = 'flex';
            cargarGruposFiltro($('#migracion_carrera').val() || '');
        }

        function migrarAlumnos() {
            const data = {
                action: 'migrar',
                id_carrera: $('#migracion_carrera').val(),
                id_grupo_origen: $('#migracion_grupo_origen').val(),
                id_grupo_destino: $('#migracion_grupo_destino').val()
            };

            if (!data.id_carrera || !data.id_grupo_origen || !data.id_grupo_destino) {
                alert('Todos los campos son obligatorios');
                return;
            }

            if (data.id_grupo_origen === data.id_grupo_destino) {
                alert('El grupo origen y destino deben ser diferentes');
                return;
            }

            if (!confirm('¿Estás seguro de migrar todos los alumnos del grupo origen al grupo destino?')) {
                return;
            }

            $.ajax({
                url: '../../ajax/alumnos.php',
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(`Migración exitosa: ${response.migrados} alumnos migrados`);
                        cerrarModal('modalMigracion');
                        cargarAlumnos();
                    } else {
                        alert('Error al migrar: ' + response.message);
                    }
                }
            });
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