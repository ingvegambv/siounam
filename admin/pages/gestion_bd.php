<?php
// admin/pages/gestion_bd.php
require_once '../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Base de Datos - SIOUNAM</title>
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
            <header-component title="Gestión de Base de Datos" icon="database">
                <div slot="actions">
                    <button class="btn btn-success btn-sm" onclick="exportarCSV()">
                        <i class="fas fa-file-export"></i> Exportar CSV
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="document.getElementById('fileInput').click()">
                        <i class="fas fa-file-import"></i> Importar CSV
                    </button>
                    <input type="file" id="fileInput" accept=".csv" style="display:none" onchange="importarCSV(this)">
                </div>
            </header-component>

            <!-- Tablas disponibles -->
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; margin-bottom:25px;">
                <div class="stat-card" onclick="cargarTabla('usuarios_unam')" style="cursor:pointer;">
                    <div class="stat-icon text-primary"><i class="fas fa-users"></i></div>
                    <div class="stat-label">Usuarios</div>
                </div>
                <div class="stat-card" onclick="cargarTabla('alumnos_unam')" style="cursor:pointer;">
                    <div class="stat-icon text-success"><i class="fas fa-user-graduate"></i></div>
                    <div class="stat-label">Alumnos</div>
                </div>
                <div class="stat-card" onclick="cargarTabla('materias')" style="cursor:pointer;">
                    <div class="stat-icon text-warning"><i class="fas fa-book"></i></div>
                    <div class="stat-label">Materias</div>
                </div>
                <div class="stat-card" onclick="cargarTabla('grupo_unam')" style="cursor:pointer;">
                    <div class="stat-icon text-danger"><i class="fas fa-layer-group"></i></div>
                    <div class="stat-label">Grupos</div>
                </div>
                <div class="stat-card" onclick="cargarTabla('carrera_unam')" style="cursor:pointer;">
                    <div class="stat-icon text-info"><i class="fas fa-building"></i></div>
                    <div class="stat-label">Carreras</div>
                </div>
                <div class="stat-card" onclick="cargarTabla('calificaciones_unam')" style="cursor:pointer;">
                    <div class="stat-icon text-purple"><i class="fas fa-star"></i></div>
                    <div class="stat-label">Calificaciones</div>
                </div>
            </div>

            <!-- Tabla de datos -->
            <table-component id="tablaBD"></table-component>
            
            <div id="infoTabla" style="margin-top:10px; color:#7f8c8d; font-size:13px;">
                Selecciona una tabla para visualizar sus datos
            </div>
        </div>
    </div>

    <script>
        let tablaActual = '';

        $(document).ready(function() {
            const tabla = document.getElementById('tablaBD');
            
            // Configurar acciones básicas
            tabla.setActions([
                { key: 'edit', icon: 'fa-edit', color: 'warning', label: 'Editar' },
                { key: 'delete', icon: 'fa-trash', color: 'danger', label: 'Eliminar' }
            ]);
            
            tabla.addEventListener('action', function(e) {
                if (e.detail.action === 'edit') {
                    alert('Editar registro ID: ' + e.detail.id);
                } else if (e.detail.action === 'delete') {
                    if (confirm('¿Eliminar este registro?')) {
                        eliminarRegistro(tablaActual, e.detail.id);
                    }
                }
            });
        });

        function cargarTabla(tabla) {
            tablaActual = tabla;
            const tablaComponent = document.getElementById('tablaBD');
            const info = document.getElementById('infoTabla');
            
            info.textContent = 'Cargando datos de ' + tabla + '...';
            
            $.ajax({
                url: '../../ajax/bd.php',
                type: 'POST',
                data: { action: 'list', tabla: tabla },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Configurar columnas dinámicamente
                        const columns = [];
                        if (response.data.length > 0) {
                            const firstRow = response.data[0];
                            Object.keys(firstRow).forEach(key => {
                                columns.push({
                                    key: key,
                                    label: key.replace(/_/g, ' ').toUpperCase()
                                });
                            });
                        }
                        
                        tablaComponent.setColumns(columns);
                        tablaComponent.setData(response.data);
                        info.textContent = `Mostrando ${response.data.length} registros de ${tabla}`;
                    } else {
                        info.textContent = 'Error al cargar datos: ' + response.message;
                    }
                },
                error: function() {
                    info.textContent = 'Error al conectar con el servidor';
                }
            });
        }

        function eliminarRegistro(tabla, id) {
            $.ajax({
                url: '../../ajax/bd.php',
                type: 'POST',
                data: { action: 'delete', tabla: tabla, id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Registro eliminado');
                        cargarTabla(tablaActual);
                    } else {
                        alert('Error al eliminar: ' + response.message);
                    }
                }
            });
        }

        function exportarCSV() {
            if (!tablaActual) {
                alert('Selecciona una tabla primero');
                return;
            }
            
            window.location.href = '../../ajax/bd.php?action=export&tabla=' + tablaActual;
        }

        function importarCSV(input) {
            if (!tablaActual) {
                alert('Selecciona una tabla primero');
                input.value = '';
                return;
            }
            
            const file = input.files[0];
            if (!file) return;
            
            const formData = new FormData();
            formData.append('action', 'import');
            formData.append('tabla', tablaActual);
            formData.append('file', file);
            
            $.ajax({
                url: '../../ajax/bd.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Importación exitosa: ' + response.message);
                        cargarTabla(tablaActual);
                    } else {
                        alert('Error al importar: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error al importar el archivo');
                }
            });
            
            input.value = '';
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