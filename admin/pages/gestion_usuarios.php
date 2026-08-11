<?php
// admin/pages/gestion_usuarios.php
require_once '../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - SIOUNAM</title>
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
            <header-component title="Gestión de Usuarios" icon="users-cog">
                <div slot="actions">
                    <button class="btn btn-primary btn-sm" onclick="abrirModal()">
                        <i class="fas fa-plus"></i> Nuevo Usuario
                    </button>
                </div>
            </header-component>

            <table-component id="tablaUsuarios"></table-component>
        </div>
    </div>

    <!-- Modal de Usuario -->
    <div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="frmUsuario" novalidate>
                        <input type="hidden" id="id_usuario" name="id_usuario">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Rol *</label>
                                    <select class="form-control" id="id_rol" name="id_rol" required>
                                        <option value="">Seleccionar rol</option>
                                        <option value="1">Administrador</option>
                                        <option value="2">Coordinador</option>
                                        <option value="3">Maestro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" id="carreraGroup" style="display:none;">
                                    <label class="form-label">Carrera</label>
                                    <select class="form-control" id="id_carrera" name="id_carrera">
                                        <option value="">Sin carrera</option>
                                    </select>
                                    <small class="text-muted" id="carreraHelp">Obligatorio para Coordinadores</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Apellido Paterno *</label>
                                    <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Apellido Materno *</label>
                                    <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Usuario *</label>
                                    <input type="text" class="form-control" id="usuario" name="usuario" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Contraseña <span id="passRequired">*</span></label>
                                    <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                                    <small class="text-muted" id="passHelp">Mínimo 6 caracteres</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Correo</label>
                                    <input type="email" class="form-control" id="correo" name="correo">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" id="telefono" name="telefono">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="activo" name="activo" value="1" checked>
                                <label class="form-check-label" for="activo">Activo</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarUsuario">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ===== CONFIGURACIÓN DE RUTAS - VERSIÓN CORREGIDA =====
        // Usamos la ruta relativa desde la raíz del proyecto
        // Desde admin/pages/ subimos 2 niveles: pages -> admin -> raíz
        const BASE_URL = '../../ajax/';
        const URL_USERS = BASE_URL + 'users.php';
        const URL_CARRERAS = BASE_URL + 'carreras.php';

        // Verificar en consola
        console.log('🔍 Verificando rutas:');
        console.log('  BASE_URL:', BASE_URL);
        console.log('  URL_USERS:', URL_USERS);
        console.log('  URL_CARRERAS:', URL_CARRERAS);

        let modalInstance = null;
        let editando = false;

        $(document).ready(function() {
            console.log('✅ Página cargada correctamente');
            
            // Inicializar modal
            const modalElement = document.getElementById('modalUsuario');
            modalInstance = new bootstrap.Modal(modalElement);
            
            // Configurar tabla
            const tabla = document.getElementById('tablaUsuarios');
            
            tabla.setColumns([
                { key: 'id_usuario', label: 'ID' },
                { key: 'nombre_completo', label: 'Nombre' },
                { key: 'usuario', label: 'Usuario' },
                { 
                    key: 'nombre_rol', 
                    label: 'Rol',
                    type: 'badge',
                    badgeMap: {
                        'ADMINISTRADOR': 'badge-danger',
                        'COORDINADOR': 'badge-warning',
                        'MAESTRO': 'badge-info'
                    }
                },
                { key: 'nombre_carrera', label: 'Carrera' },
                { key: 'correo', label: 'Correo' },
                { 
                    key: 'activo', 
                    label: 'Estado',
                    type: 'badge',
                    badgeMap: {
                        '1': 'badge-success',
                        '0': 'badge-danger'
                    }
                }
            ]);
            
            tabla.setActions([
                { key: 'edit', icon: 'fa-edit', color: 'warning', label: 'Editar' },
                { key: 'delete', icon: 'fa-trash', color: 'danger', label: 'Eliminar' }
            ]);
            
            tabla.addEventListener('action', function(e) {
                if (e.detail.action === 'edit') {
                    editarUsuario(e.detail.id);
                } else if (e.detail.action === 'delete') {
                    eliminarUsuario(e.detail.id);
                }
            });

            // Cargar datos
            cargarUsuarios();
            cargarCarreras();

            // Evento cambio de rol
            $('#id_rol').change(function() {
                const rol = $(this).val();
                if (rol == '2') {
                    $('#carreraGroup').show();
                    $('#id_carrera').prop('required', true);
                    $('#carreraHelp').text('Obligatorio para Coordinadores');
                } else {
                    $('#carreraGroup').hide();
                    $('#id_carrera').prop('required', false);
                    $('#id_carrera').val('');
                }
            });

            // Guardar usuario
            $('#btnGuardarUsuario').click(function() {
                guardarUsuario();
            });

            // Resetear formulario al cerrar modal
            modalElement.addEventListener('hidden.bs.modal', function() {
                resetFormulario();
            });
        });

        function resetFormulario() {
            $('#frmUsuario')[0].reset();
            $('#id_usuario').val('');
            $('#contrasena').val('');
            $('#contrasena').prop('required', true);
            $('#passRequired').text('*');
            $('#passHelp').text('Mínimo 6 caracteres');
            editando = false;
            $('#modalTitle').text('Nuevo Usuario');
            $('#carreraGroup').hide();
            $('#id_carrera').prop('required', false);
        }

        function cargarUsuarios() {
            console.log('🔄 Cargando usuarios desde:', URL_USERS);
            const tabla = document.getElementById('tablaUsuarios');
            
            $.ajax({
                url: URL_USERS,
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                timeout: 10000,
                success: function(data) {
                    console.log('✅ Usuarios cargados:', data.length);
                    const mappedData = data.map(user => ({
                        ...user,
                        nombre_completo: `${user.nombre} ${user.apellido_paterno}`,
                        activo: user.activo
                    }));
                    tabla.setData(mappedData);
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error al cargar usuarios:');
                    console.error('  Status:', status);
                    console.error('  Error:', error);
                    console.error('  Response:', xhr.responseText);
                    alert('Error al cargar usuarios. Revisa la consola (F12) para más detalles.');
                }
            });
        }

        function cargarCarreras() {
            console.log('🔄 Cargando carreras desde:', URL_CARRERAS);
            
            $.ajax({
                url: URL_CARRERAS,
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                timeout: 10000,
                success: function(data) {
                    console.log('✅ Carreras cargadas:', data.length);
                    let html = '<option value="">Sin carrera</option>';
                    data.forEach(function(carrera) {
                        html += `<option value="${carrera.id_carrera}">${carrera.nombre_carrera}</option>`;
                    });
                    $('#id_carrera').html(html);
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error al cargar carreras:');
                    console.error('  Status:', status);
                    console.error('  Error:', error);
                    console.error('  Response:', xhr.responseText);
                }
            });
        }

        function abrirModal() {
            resetFormulario();
            $('#modalTitle').text('Nuevo Usuario');
            $('#contrasena').prop('required', true);
            $('#passRequired').text('*');
            $('#passHelp').text('Mínimo 6 caracteres');
            modalInstance.show();
        }

        function editarUsuario(id) {
            editando = true;
            $('#modalTitle').text('Editar Usuario');
            $('#contrasena').prop('required', false);
            $('#passRequired').text('(Opcional)');
            $('#passHelp').text('Dejar en blanco para no cambiar');
            
            $.ajax({
                url: URL_USERS,
                type: 'POST',
                data: { action: 'get', id: id },
                dataType: 'json',
                success: function(data) {
                    $('#id_usuario').val(data.id_usuario);
                    $('#id_rol').val(data.id_rol);
                    
                    if (data.id_rol == 2) {
                        $('#carreraGroup').show();
                        $('#id_carrera').prop('required', true);
                        $('#id_carrera').val(data.id_carrera || '');
                    } else {
                        $('#carreraGroup').hide();
                        $('#id_carrera').prop('required', false);
                        $('#id_carrera').val('');
                    }
                    
                    $('#nombre').val(data.nombre);
                    $('#apellido_paterno').val(data.apellido_paterno);
                    $('#apellido_materno').val(data.apellido_materno);
                    $('#usuario').val(data.usuario);
                    $('#correo').val(data.correo || '');
                    $('#telefono').val(data.telefono || '');
                    $('#activo').prop('checked', data.activo == 1);
                    
                    modalInstance.show();
                },
                error: function(xhr) {
                    console.error('Error al cargar usuario:', xhr.responseText);
                    alert('Error al cargar datos del usuario');
                }
            });
        }

        function eliminarUsuario(id) {
            if (confirm('¿Estás seguro de desactivar este usuario?')) {
                $.ajax({
                    url: URL_USERS,
                    type: 'POST',
                    data: { action: 'delete', id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Usuario desactivado correctamente');
                            cargarUsuarios();
                        } else {
                            alert('Error al desactivar el usuario');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al eliminar:', xhr.responseText);
                        alert('Error al conectar con el servidor');
                    }
                });
            }
        }

        function guardarUsuario() {
            const idRol = $('#id_rol').val();
            const idCarrera = $('#id_carrera').val();
            const nombre = $('#nombre').val().trim();
            const apellidoPaterno = $('#apellido_paterno').val().trim();
            const apellidoMaterno = $('#apellido_materno').val().trim();
            const usuario = $('#usuario').val().trim();
            const contrasena = $('#contrasena').val();
            const correo = $('#correo').val().trim();
            const telefono = $('#telefono').val().trim();
            const activo = $('#activo').is(':checked') ? 1 : 0;

            // Validaciones
            if (!idRol) {
                alert('Selecciona un rol');
                return;
            }

            if (!nombre || !apellidoPaterno || !apellidoMaterno) {
                alert('Nombre, Apellido Paterno y Apellido Materno son obligatorios');
                return;
            }

            if (!usuario) {
                alert('El usuario es obligatorio');
                return;
            }

            if (!editando && (!contrasena || contrasena.length < 6)) {
                alert('La contraseña es obligatoria y debe tener al menos 6 caracteres');
                return;
            }

            if (editando && contrasena && contrasena.length < 6) {
                alert('La contraseña debe tener al menos 6 caracteres');
                return;
            }

            if (idRol == '2' && !idCarrera) {
                alert('Los Coordinadores deben tener una carrera asignada');
                return;
            }

            const data = {
                action: editando ? 'update' : 'create',
                id: $('#id_usuario').val(),
                id_rol: idRol,
                id_carrera: idCarrera || null,
                nombre: nombre,
                apellido_paterno: apellidoPaterno,
                apellido_materno: apellidoMaterno,
                usuario: usuario,
                contrasena: contrasena,
                correo: correo,
                telefono: telefono,
                activo: activo
            };

            console.log('📤 Enviando datos:', data);

            const btn = $('#btnGuardarUsuario');
            const originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> Guardando...').prop('disabled', true);

            $.ajax({
                url: URL_USERS,
                type: 'POST',
                data: data,
                dataType: 'json',
                timeout: 15000,
                success: function(response) {
                    console.log('✅ Respuesta del servidor:', response);
                    if (response.success) {
                        alert(editando ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente');
                        modalInstance.hide();
                        cargarUsuarios();
                    } else {
                        alert('Error: ' + (response.message || 'Error desconocido'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error en la petición:');
                    console.error('  Status:', status);
                    console.error('  Error:', error);
                    console.error('  Response:', xhr.responseText);
                    alert('Error al conectar con el servidor. Revisa la consola (F12) para más detalles.');
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
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