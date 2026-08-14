<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../models/Carrera.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Alumno.php';
require_once __DIR__ . '/../models/Materia.php';
require_once __DIR__ . '/../models/Grupo.php';
require_once __DIR__ . '/../models/Asignacion.php';
require_once __DIR__ . '/../models/MaestroCarrera.php';

$user = $_SESSION['usuario'];
$idCarrera = CARRERA_ID;

// Obtener datos para estadísticas
$carreraModel = new Carrera();
$userModel = new User();
$alumnoModel = new Alumno();
$materiaModel = new Materia();
$grupoModel = new Grupo();
$asignacionModel = new Asignacion();
$maestroCarreraModel = new MaestroCarrera();

$carrera = $carreraModel->getById($idCarrera);

// Estadísticas
$totalAlumnos = count($alumnoModel->getByCarrera($idCarrera));
$totalMaestros = count($userModel->getMaestrosByCarrera($idCarrera));
$totalMaterias = count($materiaModel->getByCarrera($idCarrera));
$totalGrupos = count($grupoModel->getByCarrera($idCarrera));
$totalAsignaciones = count($asignacionModel->getByCarrera($idCarrera));

// Alumnos por grupo
$alumnosPorGrupo = $alumnoModel->countByGrupo($idCarrera);

// Actividad reciente (últimos 10 alumnos registrados)
$alumnosRecientes = $alumnoModel->getRecentByCarrera($idCarrera, 10);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Coordinador</title>
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.7;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #1a237e;
        }
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        .recent-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .recent-item:last-child {
            border-bottom: none;
        }
        .badge-grupo {
            background: #e3f2fd;
            color: #1565c0;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        .grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        @media (max-width: 768px) {
            .grid-2col {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <sidebar-component></sidebar-component>
        
        <main class="main-content">
            <header-component title="Dashboard de Coordinación">
                <span slot="actions">
                    <span class="badge" style="background: #1a237e; color: white; padding: 8px 16px; border-radius: 20px;">
                        <i class="fas fa-university"></i> <?php echo htmlspecialchars($carrera['nombre_carrera'] ?? 'Carrera no asignada'); ?>
                    </span>
                </span>
            </header-component>

            <div class="content-wrapper" style="padding: 20px;">
                <!-- Tarjetas de estadísticas -->
                <div class="row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #1565c0;"><i class="fas fa-users"></i></div>
                        <div class="stat-number"><?php echo $totalAlumnos; ?></div>
                        <div class="stat-label">Total Alumnos</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #2e7d32;"><i class="fas fa-chalkboard-teacher"></i></div>
                        <div class="stat-number"><?php echo $totalMaestros; ?></div>
                        <div class="stat-label">Total Maestros</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #e65100;"><i class="fas fa-book"></i></div>
                        <div class="stat-number"><?php echo $totalMaterias; ?></div>
                        <div class="stat-label">Total Materias</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #6a1b9a;"><i class="fas fa-layer-group"></i></div>
                        <div class="stat-number"><?php echo $totalGrupos; ?></div>
                        <div class="stat-label">Total Grupos</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #c62828;"><i class="fas fa-tasks"></i></div>
                        <div class="stat-number"><?php echo $totalAsignaciones; ?></div>
                        <div class="stat-label">Asignaciones</div>
                    </div>
                </div>

                <!-- Gráfico de alumnos por grupo -->
                <div class="grid-2col">
                    <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h4 style="margin-bottom: 15px; color: #1a237e;">
                            <i class="fas fa-chart-bar"></i> Alumnos por Grupo
                        </h4>
                        <canvas id="gruposChart"></canvas>
                    </div>
                    
                    <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h4 style="margin-bottom: 15px; color: #1a237e;">
                            <i class="fas fa-clock"></i> Actividad Reciente
                        </h4>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php if (empty($alumnosRecientes)): ?>
                                <p style="color: #999; text-align: center; padding: 20px;">No hay actividad reciente</p>
                            <?php else: ?>
                                <?php foreach ($alumnosRecientes as $alumno): ?>
                                    <div class="recent-item">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <strong><?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido_paterno']); ?></strong>
                                                <br>
                                                <small style="color: #666;">Matrícula: <?php echo htmlspecialchars($alumno['matricula'] ?? 'N/A'); ?></small>
                                            </div>
                                            <span class="badge-grupo"><?php echo htmlspecialchars($alumno['nombre_grupo'] ?? 'Sin grupo'); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Alerta de pendientes -->
                <?php if ($totalAsignaciones == 0 && $totalMaterias > 0): ?>
                    <div style="background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <i class="fas fa-exclamation-triangle" style="color: #ff9800;"></i>
                        <strong>¡Atención!</strong> No hay asignaciones de maestros para las materias de esta carrera.
                        <a href="pages/asignar_materias.php" style="color: #1565c0; text-decoration: underline;">Asignar ahora</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../assets/components/sidebar-component.js"></script>
    <script src="../assets/components/header-component.js"></script>
    
    <script>
        // Datos para el gráfico
        const gruposData = <?php echo json_encode($alumnosPorGrupo); ?>;
        
        if (gruposData.length > 0) {
            const ctx = document.getElementById('gruposChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: gruposData.map(item => item.nombre_grupo || 'Sin grupo'),
                    datasets: [{
                        label: 'Alumnos',
                        data: gruposData.map(item => parseInt(item.total)),
                        backgroundColor: 'rgba(26, 35, 126, 0.7)',
                        borderColor: 'rgba(26, 35, 126, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>