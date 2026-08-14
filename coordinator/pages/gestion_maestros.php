<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/MaestroCarrera.php';
require_once __DIR__ . '/../../models/Carrera.php';

$userModel = new User();
$maestroCarreraModel = new MaestroCarrera();
$carreraModel = new Carrera();

// Obtener maestros de mi carrera
$maestros = $userModel->getMaestrosByCarrera(CARRERA_ID);
$carreras = $carreraModel->getAll();
$carrera = $carreraModel->getById(CARRERA_ID);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Maestros - Coordinador</title>
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-add {
            background: #1a237e;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 15px;
        }
        .btn-add:hover {
            background: #0d1b5e;
        }
        .btn-remove {
            background: #c62828;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn-remove:hover {
            background: #b71c1c;
        }
        
        /* ===== MODAL CON BUSCADOR ===== */
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
            max-width: 550px;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-content h3 {
            margin-top: 0;
            color: #1a237e;
        }
        .modal-content .subtitle {
            color: #666;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        /* Buscador dentro del modal */
        .search-input-wrapper {
            position: relative;
            margin-bottom: 15px;
        }
        .search-input-wrapper input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .search-input-wrapper input:focus {
            border-color: #1a237e;
            outline: none;
        }
        .search-input-wrapper .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        .search-input-wrapper .clear-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            display: none;
        }
        .search-input-wrapper .clear-btn:hover {
            color: #c62828;
        }
        .search-input-wrapper .clear-btn.show {
            display: block;
        }
        
        /* Resultados del buscador */
        .results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #1a237e;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1001;
            display: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .results-dropdown.show {
            display: block;
        }
        .results-dropdown .result-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s;
        }
        .results-dropdown .result-item:hover {
            background: #e3f2fd;
        }
        .results-dropdown .result-item .nombre {
            font-weight: 500;
            color: #333;
        }
        .results-dropdown .result-item .usuario {
            font-size: 0.8rem;
            color: #666;
            background: #f5f5f5;
            padding: 2px 10px;
            border-radius: 12px;
        }
        .results-dropdown .result-item .badge-disponible {
            font-size: 0.7rem;
            color: #2e7d32;
            background: #e8f5e9;
            padding: 2px 10px;
            border-radius: 12px;
        }
        .results-dropdown .no-results {
            padding: 20px;
            text-align: center;
            color: #666;
        }
        .results-dropdown .no-results i {
            font-size: 1.5rem;
            color: #ccc;
            margin-bottom: 10px;
        }
        .results-dropdown .loading-results {
            padding: 15px;
            text-align: center;
            color: #666;
        }
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #1a237e;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
            vertical-align: middle;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Maestro seleccionado */
        .selected-maestro {
            display: none;
            background: #e8f5e9;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #c8e6c9;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .selected-maestro.show {
            display: flex;
        }
        .selected-maestro .info .nombre {
            font-weight: 600;
            color: #1a237e;
        }
        .selected-maestro .info .usuario {
            color: #666;
            font-size: 0.9rem;
            margin-left: 10px;
        }
        .selected-maestro .btn-change {
            background: #1565c0;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .selected-maestro .btn-change:hover {
            background: #0d47a1;
        }

        .btn-save {
            background: #1a237e;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        .btn-save:hover {
            background: #0d1b5e;
        }
        .btn-save:disabled {
            opacity: 0.5;
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
        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
            border-top: 1px solid #edf2f7;
            padding-top: 20px;
        }

        /* Tabla */
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
        .alert-success {
            background: #e8f5e9;
            border-left: 4px solid #2e7d32;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            color: #1b5e20;
        }
        .alert-danger {
            background: #ffebee;
            border-left: 4px solid #c62828;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            color: #b71c1c;
        }
        .badge-carrera {
            font-size: 0.7rem;
            color: #1565c0;
            background: #e3f2fd;
            padding: 2px 8px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <sidebar-component></sidebar-component>
        
        <main class="main-content">
            <header-component title="Gestión de Maestros">
                <span slot="actions">
                    <span class="badge" style="background: #1a237e; color: white; padding: 8px 16px; border-radius: 20px;">
                        <i class="fas fa-university"></i> <?php echo CARRERA_NOMBRE; ?>
                    </span>
                </span>
            </header-component>

            <div class="content-wrapper" style="padding: 20px;">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <div class="table-container">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                        <h3 style="margin: 0; color: #1a237e;">
                            <i class="fas fa-chalkboard-teacher"></i> Maestros Asignados
                            <span style="font-size: 0.9rem; color: #666; font-weight: normal;">
                                (<?php echo count($maestros); ?>)
                            </span>
                        </h3>
                        <button class="btn-add" onclick="openModal()">
                            <i class="fas fa-plus"></i> Asignar Maestro
                        </button>
                    </div>

                    <?php if (empty($maestros)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <p>No hay maestros asignados a esta carrera.</p>
                            <button class="btn-add" onclick="openModal()" style="margin-top: 10px;">
                                <i class="fas fa-plus"></i> Asignar primer maestro
                            </button>
                        </div>
                    <?php else: ?>
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f5f5f5;">
                                    <th style="padding: 10px; text-align: left;">#</th>
                                    <th style="padding: 10px; text-align: left;">Nombre</th>
                                    <th style="padding: 10px; text-align: left;">Usuario</th>
                                    <th style="padding: 10px; text-align: left;">Correo</th>
                                    <th style="padding: 10px; text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $contador = 1; ?>
                                <?php foreach ($maestros as $maestro): ?>
                                <tr>
                                    <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo $contador++; ?></td>
                                    <td style="padding: 10px; border-bottom: 1px solid #eee;">
                                        <?php echo htmlspecialchars($maestro['nombre'] . ' ' . $maestro['apellido_paterno'] . ' ' . $maestro['apellido_materno']); ?>
                                    </td>
                                    <td style="padding: 10px; border-bottom: 1px solid #eee;">
                                        <code><?php echo htmlspecialchars($maestro['usuario']); ?></code>
                                    </td>
                                    <td style="padding: 10px; border-bottom: 1px solid #eee;">
                                        <?php echo htmlspecialchars($maestro['correo'] ?? 'N/A'); ?>
                                    </td>
                                    <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: center;">
                                        <button class="btn-remove" onclick="removeMaestro(<?php echo $maestro['id_usuario']; ?>)">
                                            <i class="fas fa-user-minus"></i> Desasignar
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Asignar Maestro CON BUSCADOR -->
    <div class="modal" id="assignModal">
        <div class="modal-content">
            <h3><i class="fas fa-user-plus"></i> Asignar Maestro</h3>
            <p class="subtitle">Busca un maestro por nombre o usuario para asignarlo a <strong><?php echo CARRERA_NOMBRE; ?></strong></p>
            
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="maestroSearchInput" 
                       placeholder="Buscar maestro por nombre o usuario..." 
                       autocomplete="off">
                <button class="clear-btn" id="clearSearchBtn" onclick="clearMaestroSearch()">
                    <i class="fas fa-times-circle"></i>
                </button>
                <div class="results-dropdown" id="maestroResults">
                    <!-- Resultados dinámicos -->
                </div>
            </div>

            <!-- Maestro seleccionado -->
            <div class="selected-maestro" id="selectedMaestro">
                <div class="info">
                    <span class="nombre" id="selectedNombre">-</span>
                    <span class="usuario" id="selectedUsuario">@usuario</span>
                </div>
                <button class="btn-change" onclick="clearSelectedMaestro()">
                    <i class="fas fa-exchange-alt"></i> Cambiar
                </button>
            </div>

            <form id="assignForm">
                <input type="hidden" id="selectedMaestroId" name="id_usuario" value="">
                <input type="hidden" name="action" value="assign">
                <input type="hidden" name="id_carrera" value="<?php echo CARRERA_ID; ?>">
                
                <div style="margin-top: 20px; border-top: 1px solid #edf2f7; padding-top: 20px;">
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                        <button type="submit" class="btn-save" id="btnAssignMaestro" disabled>
                            <i class="fas fa-save"></i> Asignar Maestro
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/components/sidebar-component.js"></script>
    <script src="../../assets/components/header-component.js"></script>
    
    <script>
        const CARRERA_ID = <?php echo CARRERA_ID; ?>;
        let searchTimeout = null;
        let selectedMaestroId = null;

        // ===== BUSCADOR DE MAESTROS =====
        function searchMaestros(query) {
            const resultsContainer = document.getElementById('maestroResults');
            
            if (query.length < 2) {
                resultsContainer.classList.remove('show');
                return;
            }

            resultsContainer.innerHTML = `
                <div class="loading-results">
                    <div class="spinner"></div>
                    Buscando maestros...
                </div>
            `;
            resultsContainer.classList.add('show');

            $.ajax({
                url: '../../ajax/maestros_carrera.php',
                type: 'POST',
                data: {
                    action: 'search_available',
                    query: query,
                    id_carrera: CARRERA_ID
                },
                dataType: 'json',
                success: function(response) {
                    renderMaestroResults(response);
                },
                error: function() {
                    resultsContainer.innerHTML = `
                        <div class="no-results">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>Error al buscar. Intenta de nuevo.</p>
                        </div>
                    `;
                }
            });
        }

        function renderMaestroResults(data) {
            const resultsContainer = document.getElementById('maestroResults');
            
            if (!data || data.length === 0) {
                resultsContainer.innerHTML = `
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <p>No se encontraron maestros disponibles con "<strong>${document.getElementById('maestroSearchInput').value}</strong>"</p>
                        <p style="font-size: 0.8rem; color: #999; margin-top: 5px;">
                            <i class="fas fa-info-circle"></i> 
                            Solo se muestran maestros que no están asignados a esta carrera
                        </p>
                    </div>
                `;
                return;
            }

            let html = '';
            data.forEach(function(maestro) {
                const nombreCompleto = `${maestro.nombre} ${maestro.apellido_paterno} ${maestro.apellido_materno || ''}`;
                const usuario = maestro.usuario || 'sin-usuario';
                
                html += `
                    <div class="result-item" onclick="selectMaestro(${maestro.id_usuario}, '${nombreCompleto}', '${usuario}')">
                        <div>
                            <span class="nombre">${nombreCompleto}</span>
                            <span class="usuario">@${usuario}</span>
                        </div>
                        <span class="badge-disponible">
                            <i class="fas fa-check-circle"></i> Disponible
                        </span>
                    </div>
                `;
            });

            resultsContainer.innerHTML = html;
        }

        function selectMaestro(id, nombre, usuario) {
            selectedMaestroId = id;
            
            // Mostrar seleccionado
            document.getElementById('selectedNombre').textContent = nombre;
            document.getElementById('selectedUsuario').textContent = '@' + usuario;
            document.getElementById('selectedMaestroId').value = id;
            document.getElementById('selectedMaestro').classList.add('show');
            document.getElementById('btnAssignMaestro').disabled = false;
            
            // Limpiar búsqueda
            document.getElementById('maestroSearchInput').value = '';
            document.getElementById('maestroResults').classList.remove('show');
            document.getElementById('clearSearchBtn').classList.remove('show');
        }

        function clearSelectedMaestro() {
            selectedMaestroId = null;
            document.getElementById('selectedMaestro').classList.remove('show');
            document.getElementById('selectedMaestroId').value = '';
            document.getElementById('btnAssignMaestro').disabled = true;
            document.getElementById('maestroSearchInput').focus();
        }

        function clearMaestroSearch() {
            document.getElementById('maestroSearchInput').value = '';
            document.getElementById('maestroResults').classList.remove('show');
            document.getElementById('clearSearchBtn').classList.remove('show');
            document.getElementById('maestroSearchInput').focus();
        }

        // ===== MODAL =====
        function openModal() {
            document.getElementById('assignModal').classList.add('active');
            // Limpiar selección anterior
            clearSelectedMaestro();
            document.getElementById('maestroSearchInput').value = '';
            document.getElementById('maestroResults').classList.remove('show');
            setTimeout(() => {
                document.getElementById('maestroSearchInput').focus();
            }, 100);
        }

        function closeModal() {
            document.getElementById('assignModal').classList.remove('active');
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('assignModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // ===== EVENTOS DEL BUSCADOR =====
        document.getElementById('maestroSearchInput').addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query.length > 0) {
                document.getElementById('clearSearchBtn').classList.add('show');
            } else {
                document.getElementById('clearSearchBtn').classList.remove('show');
                document.getElementById('maestroResults').classList.remove('show');
                return;
            }

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchMaestros(query);
            }, 300);
        });

        document.getElementById('maestroSearchInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const firstResult = document.querySelector('#maestroResults .result-item');
                if (firstResult) {
                    firstResult.click();
                }
            }
            if (e.key === 'Escape') {
                document.getElementById('maestroResults').classList.remove('show');
                this.blur();
            }
        });

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-input-wrapper')) {
                document.getElementById('maestroResults').classList.remove('show');
            }
        });

        // ===== ASIGNAR MAESTRO =====
        document.getElementById('assignForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const idUsuario = document.getElementById('selectedMaestroId').value;
            if (!idUsuario) {
                alert('Por favor selecciona un maestro.');
                return;
            }

            const btn = document.getElementById('btnAssignMaestro');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Asignando...';

            const formData = new FormData(this);

            $.ajax({
                url: '../../ajax/maestros_carrera.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('✅ Maestro asignado correctamente.');
                        location.reload();
                    } else {
                        alert('❌ Error: El maestro ya está asignado a esta carrera o ocurrió un error.');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-save"></i> Asignar Maestro';
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    alert('❌ Error de conexión. Revisa la consola para más detalles.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save"></i> Asignar Maestro';
                }
            });
        });

        // ===== DESASIGNAR MAESTRO =====
        function removeMaestro(idUsuario) {
            if (!confirm('¿Estás seguro de que deseas desasignar a este maestro de la carrera?')) {
                return;
            }

            $.ajax({
                url: '../../ajax/maestros_carrera.php',
                type: 'POST',
                data: {
                    action: 'delete_by_usuario',
                    id_usuario: idUsuario,
                    id_carrera: CARRERA_ID
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('✅ Maestro desasignado correctamente.');
                        location.reload();
                    } else {
                        alert('❌ Error al desasignar maestro.');
                    }
                },
                error: function() {
                    alert('❌ Error de conexión.');
                }
            });
        }

        // Debug
        console.log('Carrera ID:', CARRERA_ID);
        console.log('Carrera Nombre:', '<?php echo CARRERA_NOMBRE; ?>');
    </script>
</body>
</html>