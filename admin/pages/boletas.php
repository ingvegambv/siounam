<?php
// admin/pages/boletas.php
require_once '../includes/auth_check.php';
require_once __DIR__ . '/../../models/Alumno.php';
require_once __DIR__ . '/../../models/Asignacion.php';
require_once __DIR__ . '/../../models/Calificacion.php';
require_once __DIR__ . '/../../models/Materia.php';
require_once __DIR__ . '/../../models/Grupo.php';
require_once __DIR__ . '/../../models/Carrera.php';

$alumnoModel = new Alumno();
$asignacionModel = new Asignacion();
$calificacionModel = new Calificacion();
$materiaModel = new Materia();
$grupoModel = new Grupo();
$carreraModel = new Carrera();

// Obtener parámetros
$idAlumno = isset($_GET['id_alumno']) ? (int)$_GET['id_alumno'] : null;
$semestre = isset($_GET['semestre']) ? (int)$_GET['semestre'] : null;

// Datos del alumno seleccionado
$alumnoData = null;
$materias = [];
$semestresDisponibles = [];
$alumnos = $alumnoModel->getAll();

if ($idAlumno) {
    $alumnoData = $alumnoModel->getById($idAlumno);
    if ($alumnoData) {
        $semestresDisponibles = $alumnoModel->getSemestresWithMaterias($idAlumno);
        
        if (!$semestre && !empty($semestresDisponibles)) {
            $semestre = $semestresDisponibles[0]['id_semestre'];
        }
        
        if ($semestre) {
            $materias = $alumnoModel->getMateriasByAlumnoSemestre($idAlumno, $semestre);
            
            foreach ($materias as &$materia) {
                $calificacion = $calificacionModel->getByAlumnoMateria($idAlumno, $materia['id_materia']);
                $materia['calificacion'] = $calificacion;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boletas - SIOUNAM</title>
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/components/sidebar-component.js" defer></script>
    <script src="../../assets/components/header-component.js" defer></script>
    <style>
        /* ===== SEARCH SECTION ===== */
        .search-section {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        .search-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .search-input {
            position: relative;
            flex: 1;
            min-width: 250px;
        }
        .search-input input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .search-input input:focus {
            border-color: #1a237e;
            outline: none;
        }
        .search-input .icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        .search-input .clear-btn {
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
        .search-input .clear-btn:hover {
            color: #c62828;
        }
        .search-input .clear-btn.show {
            display: block;
        }
        .results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #1a237e;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .results-dropdown.show {
            display: block;
        }
        .result-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s;
        }
        .result-item:hover {
            background: #e3f2fd;
        }
        .result-item .matricula {
            font-weight: 600;
            color: #1a237e;
        }
        .result-item .grupo {
            font-size: 0.8rem;
            color: #666;
            background: #f5f5f5;
            padding: 2px 10px;
            border-radius: 12px;
        }
        .no-results {
            padding: 20px;
            text-align: center;
            color: #666;
        }
        .no-results i {
            font-size: 1.5rem;
            color: #ccc;
            margin-bottom: 10px;
        }
        .selected-alumno {
            display: none;
            background: #e8f5e9;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #c8e6c9;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .selected-alumno.show {
            display: flex;
        }
        .selected-alumno .info {
            flex: 1;
        }
        .selected-alumno .info .matricula-label {
            color: #2e7d32;
            font-weight: 600;
        }
        .selected-alumno .btn-change {
            background: #1565c0;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .selected-alumno .btn-change:hover {
            background: #0d47a1;
        }
        .semestre-selector {
            display: none;
            align-items: center;
            gap: 10px;
        }
        .semestre-selector.show {
            display: flex;
        }
        .semestre-selector select {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .semestre-selector .auto-detect {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 8px 15px;
            border-radius: 5px;
            border: 1px solid #c8e6c9;
            font-size: 0.9rem;
        }
        .btn-print {
            background: #2e7d32;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: none;
        }
        .btn-print.show {
            display: inline-block;
        }
        .btn-print:hover {
            background: #1b5e20;
        }

        /* ===== BOLETA ===== */
        .boleta-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        .boleta-header {
            border-bottom: 2px solid #1a237e;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .boleta-header h2 {
            margin: 0;
            color: #1a237e;
        }
        .boleta-header p {
            margin: 5px 0 0;
            color: #666;
        }
        .boleta-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
        }
        .boleta-info-item {
            display: flex;
            flex-direction: column;
        }
        .boleta-info-item label {
            font-size: 0.75rem;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .boleta-info-item span {
            font-size: 1rem;
            color: #1a237e;
            font-weight: 500;
        }
        .table-boleta {
            width: 100%;
            border-collapse: collapse;
        }
        .table-boleta th {
            background: #1a237e;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 13px;
        }
        .table-boleta td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        .table-boleta tr:hover {
            background: #f5f5f5;
        }
        .aprobado {
            color: #2e7d32;
            font-weight: bold;
        }
        .reprobado {
            color: #c62828;
            font-weight: bold;
        }
        .sin-calificar {
            color: #999;
            font-style: italic;
        }
        .no-data {
            background: #fff3e0;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }
        .no-data i {
            font-size: 2rem;
            color: #ff9800;
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
        @media print {
            .search-section, .app-layout sidebar-component, .app-layout header-component {
                display: none !important;
            }
            .boleta-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
            .table-boleta th {
                background: #333 !important;
            }
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <sidebar-component base-path="../../"></sidebar-component>
        <div class="main-content" id="mainContent">
            <header-component title="Boletas de Calificaciones" icon="file-alt">
                <div slot="actions">
                    <span class="badge" style="background: #1a237e; color: white; padding: 8px 16px; border-radius: 20px;">
                        <i class="fas fa-print"></i> Visualización
                    </span>
                </div>
            </header-component>

            <div style="padding: 20px;">
                <!-- Buscador -->
                <div class="search-section">
                    <div class="search-wrapper">
                        <div class="search-input">
                            <i class="fas fa-search icon"></i>
                            <input type="text" id="searchInput" 
                                   placeholder="Buscar por matrícula o nombre..." 
                                   autocomplete="off">
                            <button class="clear-btn" id="clearBtn" onclick="clearSearch()">
                                <i class="fas fa-times-circle"></i>
                            </button>
                            <div class="results-dropdown" id="resultsDropdown">
                                <!-- Resultados dinámicos -->
                            </div>
                        </div>

                        <!-- Alumno seleccionado -->
                        <div class="selected-alumno <?php echo $idAlumno ? 'show' : ''; ?>" id="selectedAlumno">
                            <div class="info">
                                <span class="matricula-label">
                                    <i class="fas fa-id-card"></i> 
                                    <?php echo htmlspecialchars($alumnoData['matricula'] ?? 'N/A'); ?>
                                </span>
                                <strong><?php echo htmlspecialchars(($alumnoData['nombre'] ?? '') . ' ' . ($alumnoData['apellido_paterno'] ?? '')); ?></strong>
                                <span style="color: #666; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($alumnoData['nombre_grupo'] ?? ''); ?>
                                </span>
                            </div>
                            <button class="btn-change" onclick="changeAlumno()">
                                <i class="fas fa-exchange-alt"></i> Cambiar
                            </button>
                        </div>

                        <!-- Selector de semestre -->
                        <div class="semestre-selector <?php echo $idAlumno && !empty($semestresDisponibles) ? 'show' : ''; ?>" id="semestreSelector">
                            <label for="semestreSelect" style="font-weight: 500; color: #333;">Semestre:</label>
                            <select id="semestreSelect" onchange="changeSemestre()">
                                <?php foreach ($semestresDisponibles as $sem): ?>
                                    <option value="<?php echo $sem['id_semestre']; ?>" 
                                            <?php echo ($semestre == $sem['id_semestre']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sem['nombre_semestre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($semestresDisponibles)): ?>
                                <span class="auto-detect">
                                    <i class="fas fa-info-circle"></i>
                                    Semestre actual: <?php echo htmlspecialchars($semestresDisponibles[0]['nombre_semestre']); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Botón imprimir -->
                        <button class="btn-print <?php echo $idAlumno ? 'show' : ''; ?>" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>

                <!-- Boleta -->
                <?php if ($idAlumno && $alumnoData && !empty($materias)): ?>
                    <div class="boleta-card" id="boleta">
                        <div class="boleta-header">
                            <h2><i class="fas fa-file-alt"></i> Boleta de Calificaciones</h2>
                            <p><?php echo htmlspecialchars($alumnoData['nombre_carrera'] ?? 'Carrera no definida'); ?></p>
                        </div>

                        <div class="boleta-info">
                            <div class="boleta-info-item">
                                <label>Alumno</label>
                                <span><?php echo htmlspecialchars($alumnoData['nombre'] . ' ' . $alumnoData['apellido_paterno'] . ' ' . $alumnoData['apellido_materno']); ?></span>
                            </div>
                            <div class="boleta-info-item">
                                <label>Matrícula</label>
                                <span><?php echo htmlspecialchars($alumnoData['matricula'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="boleta-info-item">
                                <label>ID</label>
                                <span>#<?php echo $alumnoData['id_alumno']; ?></span>
                            </div>
                            <div class="boleta-info-item">
                                <label>Grupo</label>
                                <span><?php echo htmlspecialchars($alumnoData['nombre_grupo'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="boleta-info-item">
                                <label>Semestre</label>
                                <span><?php 
                                    $semNombre = '';
                                    foreach ($semestresDisponibles as $sem) {
                                        if ($sem['id_semestre'] == $semestre) {
                                            $semNombre = $sem['nombre_semestre'];
                                            break;
                                        }
                                    }
                                    echo htmlspecialchars($semNombre ?: 'N/A'); 
                                ?></span>
                            </div>
                        </div>

                        <table class="table-boleta">
                            <thead>
                                <tr>
                                    <th>Materia</th>
                                    <th>P1</th>
                                    <th>P2</th>
                                    <th>P3</th>
                                    <th>P4</th>
                                    <th>Promedio</th>
                                    <th>Final</th>
                                    <th>Maestro</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $aprobadas = 0;
                                $reprobadas = 0;
                                $totalPromedios = 0;
                                $countMaterias = 0;
                                
                                foreach ($materias as $materia): 
                                    $calif = $materia['calificacion'] ?? null;
                                    $promedio = $calif ? $calif['promediofinal'] : null;
                                    $estado = 'Sin calificar';
                                    $claseEstado = 'sin-calificar';
                                    
                                    if ($promedio !== null && $promedio !== '') {
                                        if ($promedio >= 6) {
                                            $estado = 'Aprobado';
                                            $claseEstado = 'aprobado';
                                            $aprobadas++;
                                        } else {
                                            $estado = 'Reprobado';
                                            $claseEstado = 'reprobado';
                                            $reprobadas++;
                                        }
                                        $totalPromedios += floatval($promedio);
                                        $countMaterias++;
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($materia['nombre_materia']); ?></td>
                                        <td><?php echo isset($calif['parcial1']) && $calif['parcial1'] !== null ? number_format($calif['parcial1'], 2) : 'N/A'; ?></td>
                                        <td><?php echo isset($calif['parcial2']) && $calif['parcial2'] !== null ? number_format($calif['parcial2'], 2) : 'N/A'; ?></td>
                                        <td><?php echo isset($calif['parcial3']) && $calif['parcial3'] !== null ? number_format($calif['parcial3'], 2) : 'N/A'; ?></td>
                                        <td><?php echo isset($calif['parcial4']) && $calif['parcial4'] !== null ? number_format($calif['parcial4'], 2) : 'N/A'; ?></td>
                                        <td><?php echo isset($calif['promedioparciales']) && $calif['promedioparciales'] !== null ? number_format($calif['promedioparciales'], 2) : 'N/A'; ?></td>
                                        <td><strong><?php echo $promedio !== null ? number_format(floatval($promedio), 2) : 'N/A'; ?></strong></td>
                                        <td><?php echo htmlspecialchars($materia['maestro'] ?? 'No asignado'); ?></td>
                                        <td class="<?php echo $claseEstado; ?>">
                                            <?php echo $estado; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background: #f5f5f5; font-weight: bold;">
                                    <td colspan="8">
                                        Resumen:
                                    </td>
                                    <td>
                                        <?php if ($countMaterias > 0): ?>
                                            <span class="aprobado">Aprobadas: <?php echo $aprobadas; ?></span><br>
                                            <span class="reprobado">Reprobadas: <?php echo $reprobadas; ?></span><br>
                                            <span>Promedio: <?php echo number_format($totalPromedios / $countMaterias, 2); ?></span>
                                        <?php else: ?>
                                            Sin calificaciones
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php elseif ($idAlumno && $alumnoData && empty($materias)): ?>
                    <div class="no-data">
                        <i class="fas fa-info-circle"></i>
                        <p style="margin-top: 10px;">No hay materias registradas para este semestre.</p>
                        <?php if (!empty($semestresDisponibles)): ?>
                            <p style="color: #666;">El alumno tiene materias en otros semestres. Selecciona uno del menú.</p>
                        <?php endif; ?>
                    </div>
                <?php elseif ($idAlumno && !$alumnoData): ?>
                    <div style="background: #ffebee; padding: 20px; border-radius: 10px; text-align: center;">
                        <i class="fas fa-exclamation-circle" style="color: #c62828; font-size: 2rem;"></i>
                        <p style="margin-top: 10px;">Alumno no encontrado.</p>
                    </div>
                <?php else: ?>
                    <div style="background: #e3f2fd; padding: 30px; border-radius: 10px; text-align: center;">
                        <i class="fas fa-search" style="color: #1565c0; font-size: 2rem;"></i>
                        <p style="margin-top: 10px;">Busca un alumno por matrícula o nombre para ver su boleta.</p>
                        <p style="color: #666; font-size: 0.9rem;">Escribe al menos 2 caracteres para empezar a buscar.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        let searchTimeout = null;
        const searchInput = document.getElementById('searchInput');
        const resultsDropdown = document.getElementById('resultsDropdown');
        const clearBtn = document.getElementById('clearBtn');

        function searchAlumnos(query) {
            if (query.length < 2) {
                resultsDropdown.classList.remove('show');
                return;
            }

            resultsDropdown.innerHTML = `
                <div style="padding:15px; text-align:center; color:#666;">
                    <div class="spinner"></div>
                    Buscando...
                </div>
            `;
            resultsDropdown.classList.add('show');

            $.ajax({
                url: '../../ajax/alumnos.php',
                type: 'POST',
                data: {
                    action: 'search',
                    query: query,
                    id_carrera: null
                },
                dataType: 'json',
                success: function(response) {
                    renderResults(response);
                },
                error: function() {
                    resultsDropdown.innerHTML = `
                        <div class="no-results">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>Error al buscar. Intenta de nuevo.</p>
                        </div>
                    `;
                }
            });
        }

        function renderResults(data) {
            if (!data || data.length === 0) {
                resultsDropdown.innerHTML = `
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <p>No se encontraron alumnos con "<strong>${searchInput.value}</strong>"</p>
                    </div>
                `;
                return;
            }

            let html = '';
            data.forEach(function(alumno) {
                const nombreCompleto = `${alumno.nombre} ${alumno.apellido_paterno} ${alumno.apellido_materno || ''}`;
                const matricula = alumno.matricula || 'S/M';
                const grupo = alumno.nombre_grupo || 'Sin grupo';
                const carrera = alumno.nombre_carrera || '';
                
                html += `
                    <div class="result-item" onclick="selectAlumno(${alumno.id_alumno})">
                        <div>
                            <span class="matricula">${matricula}</span>
                            <span>${nombreCompleto}</span>
                            <span style="font-size:0.7rem; color:#999;">#${alumno.id_alumno}</span>
                            <span style="font-size:0.7rem; color:#666; margin-left:5px;">${carrera}</span>
                        </div>
                        <span class="grupo">${grupo}</span>
                    </div>
                `;
            });

            resultsDropdown.innerHTML = html;
        }

        function selectAlumno(id) {
            window.location.href = `boletas.php?id_alumno=${id}`;
        }

        function changeAlumno() {
            window.location.href = 'boletas.php';
        }

        function changeSemestre() {
            const semestre = document.getElementById('semestreSelect').value;
            const idAlumno = <?php echo $idAlumno ? $idAlumno : 0; ?>;
            if (idAlumno && semestre) {
                window.location.href = `boletas.php?id_alumno=${idAlumno}&semestre=${semestre}`;
            }
        }

        function clearSearch() {
            searchInput.value = '';
            resultsDropdown.classList.remove('show');
            clearBtn.classList.remove('show');
            searchInput.focus();
        }

        // Event listeners
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query.length > 0) {
                clearBtn.classList.add('show');
            } else {
                clearBtn.classList.remove('show');
                resultsDropdown.classList.remove('show');
                return;
            }

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchAlumnos(query);
            }, 300);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const firstResult = resultsDropdown.querySelector('.result-item');
                if (firstResult) {
                    firstResult.click();
                }
            }
            if (e.key === 'Escape') {
                resultsDropdown.classList.remove('show');
                this.blur();
            }
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-input')) {
                resultsDropdown.classList.remove('show');
            }
        });

        <?php if (!$idAlumno): ?>
            searchInput.focus();
        <?php endif; ?>
    </script>
</body>
</html>