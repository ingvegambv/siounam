<?php
// admin/pages/estadisticas.php
require_once '../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas - SIOUNAM</title>
    <link rel="stylesheet" href="../../assets/css/components.css">
    <script src="../../assets/components/sidebar-component.js" defer></script>
    <script src="../../assets/components/header-component.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }
        
        .chart-container canvas {
            width: 100% !important;
            height: 100% !important;
        }
        
        .chart-wrapper {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.03);
        }
        
        .chart-wrapper h5 {
            margin-bottom: 15px;
            color: #1a1a2e;
            font-weight: 600;
        }
        
        .chart-grid-2 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .chart-grid-1 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        @media (max-width: 768px) {
            .chart-grid-2 {
                grid-template-columns: 1fr;
            }
            .chart-grid-1 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="app-layout">
    <sidebar-component base-path="../../"></sidebar-component>
        <div class="main-content" id="mainContent">
            <header-component title="Estadísticas" icon="chart-bar">
                <div slot="actions">
                    <button class="btn btn-primary btn-sm" onclick="actualizarEstadisticas()">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </header-component>

            <!-- Filtros -->
            <div style="background:#fff; padding:20px; border-radius:15px; margin-bottom:25px; box-shadow:0 2px 15px rgba(0,0,0,0.05);">
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px;">
                    <div class="form-group">
                        <label class="form-label">Carrera</label>
                        <select class="form-control" id="filtroCarrera">
                            <option value="">Todas</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Semestre</label>
                        <select class="form-control" id="filtroSemestre">
                            <option value="">Todos</option>
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
                    <div class="form-group" style="display:flex; align-items:flex-end;">
                        <button class="btn btn-primary" onclick="aplicarFiltros()">
                            <i class="fas fa-filter"></i> Aplicar Filtros
                        </button>
                    </div>
                </div>
            </div>

            <!-- Resumen -->
            <div class="card-grid" id="resumenEstadisticas">
                <div class="stat-card fade-in-up">
                    <div class="stat-icon text-primary"><i class="fas fa-users"></i></div>
                    <div class="stat-number" id="totalEstudiantes">0</div>
                    <div class="stat-label">Total Estudiantes</div>
                </div>
                <div class="stat-card fade-in-up">
                    <div class="stat-icon text-success"><i class="fas fa-graduation-cap"></i></div>
                    <div class="stat-number" id="promedioGeneral">0.0</div>
                    <div class="stat-label">Promedio General</div>
                </div>
                <div class="stat-card fade-in-up">
                    <div class="stat-icon text-warning"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-number" id="tasaAprobacion">0%</div>
                    <div class="stat-label">Tasa de Aprobación</div>
                </div>
                <div class="stat-card fade-in-up">
                    <div class="stat-icon text-danger"><i class="fas fa-times-circle"></i></div>
                    <div class="stat-number" id="tasaReprobacion">0%</div>
                    <div class="stat-label">Tasa de Reprobación</div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="chart-grid-2">
                <div class="chart-wrapper">
                    <h5><i class="fas fa-chart-line text-primary me-2"></i>Rendimiento por Materia</h5>
                    <div class="chart-container">
                        <canvas id="chartRendimiento"></canvas>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <h5><i class="fas fa-chart-pie text-primary me-2"></i>Distribución por Carrera</h5>
                    <div class="chart-container">
                        <canvas id="chartCarreras"></canvas>
                    </div>
                </div>
            </div>

            <div class="chart-grid-1">
                <div class="chart-wrapper">
                    <h5><i class="fas fa-chart-bar text-primary me-2"></i>Calificaciones por Rango</h5>
                    <div class="chart-container">
                        <canvas id="chartCalificaciones"></canvas>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <h5><i class="fas fa-trophy text-primary me-2"></i>Top 10 Mejores Alumnos</h5>
                    <div id="topAlumnos" style="margin-top:10px; max-height:250px; overflow-y:auto;">
                        <p class="text-muted">Cargando datos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let chartRendimiento = null;
        let chartCarreras = null;
        let chartCalificaciones = null;

        $(document).ready(function() {
            cargarCarreras();
            
            setTimeout(function() {
                actualizarEstadisticas();
            }, 100);
        });

        function cargarCarreras() {
            $.ajax({
                url: '../../ajax/carreras.php',
                type: 'POST',
                data: { action: 'list' },
                dataType: 'json',
                success: function(data) {
                    let html = '<option value="">Todas</option>';
                    data.forEach(function(c) {
                        html += `<option value="${c.id_carrera}">${c.nombre_carrera}</option>`;
                    });
                    $('#filtroCarrera').html(html);
                }
            });
        }

        function aplicarFiltros() {
            actualizarEstadisticas();
        }

        function actualizarEstadisticas() {
            const filtros = {
                id_carrera: $('#filtroCarrera').val(),
                id_semestre: $('#filtroSemestre').val()
            };

            $.ajax({
                url: '../../ajax/estadisticas.php',
                type: 'POST',
                data: { 
                    action: 'getStats',
                    filtros: filtros
                },
                dataType: 'json',
                success: function(data) {
                    // Actualizar resumen
                    $('#totalEstudiantes').text(data.totalEstudiantes || 0);
                    $('#promedioGeneral').text(data.promedioGeneral ? data.promedioGeneral.toFixed(1) : '0.0');
                    $('#tasaAprobacion').text(data.tasaAprobacion ? data.tasaAprobacion.toFixed(1) + '%' : '0%');
                    $('#tasaReprobacion').text(data.tasaReprobacion ? data.tasaReprobacion.toFixed(1) + '%' : '0%');
                    
                    // Actualizar gráficos
                    actualizarGraficos(data);
                    
                    // Actualizar top alumnos
                    actualizarTopAlumnos(data.topAlumnos || []);
                },
                error: function() {
                    console.error('Error al cargar estadísticas');
                }
            });
        }

        function actualizarGraficos(data) {
            const colors = ['#667eea', '#2ecc71', '#f39c12', '#e74c3c', '#3498db', '#9b59b6', '#1abc9c', '#e67e22', '#2c3e50', '#95a5a6'];
            
            // Gráfico de rendimiento por materia
            const ctx1 = document.getElementById('chartRendimiento').getContext('2d');
            if (chartRendimiento) {
                chartRendimiento.destroy();
                chartRendimiento = null;
            }
            
            const materiasLabels = data.materiasLabels || ['Matemáticas', 'Programación', 'Bases de Datos', 'Redes', 'Sistemas', 'Inglés', 'Física'];
            const materiasData = data.materiasData || [8.5, 7.8, 9.2, 7.5, 8.0, 6.5, 7.0];
            
            chartRendimiento = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: materiasLabels,
                    datasets: [{
                        label: 'Promedio',
                        data: materiasData,
                        backgroundColor: colors.slice(0, materiasLabels.length),
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            cornerRadius: 8,
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    return `Promedio: ${context.parsed.y.toFixed(1)}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10,
                            ticks: { 
                                stepSize: 2,
                                font: { size: 11 }
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { size: 11 },
                                maxRotation: 45,
                                minRotation: 0
                            }
                        }
                    }
                }
            });

            // Gráfico de distribución por carrera
            const ctx2 = document.getElementById('chartCarreras').getContext('2d');
            if (chartCarreras) {
                chartCarreras.destroy();
                chartCarreras = null;
            }
            
            const carrerasLabels = data.carrerasLabels || ['Ing. Sistemas', 'Ing. Civil', 'Arquitectura', 'Derecho', 'Medicina', 'Otras'];
            const carrerasData = data.carrerasData || [35, 25, 20, 15, 10, 5];
            
            chartCarreras = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: carrerasLabels,
                    datasets: [{
                        data: carrerasData,
                        backgroundColor: colors.slice(0, carrerasLabels.length),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 10,
                                font: { size: 11 },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            cornerRadius: 8,
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return `${context.label}: ${context.parsed} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });

            // Gráfico de calificaciones por rango
            const ctx3 = document.getElementById('chartCalificaciones').getContext('2d');
            if (chartCalificaciones) {
                chartCalificaciones.destroy();
                chartCalificaciones = null;
            }
            
            const rangosData = data.rangosData || [5, 15, 45, 35];
            
            chartCalificaciones = new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: ['0-4', '5-6', '7-8', '9-10'],
                    datasets: [{
                        label: 'Estudiantes',
                        data: rangosData,
                        backgroundColor: ['#e74c3c', '#f39c12', '#2ecc71', '#667eea'],
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            cornerRadius: 8,
                            padding: 10
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { 
                                stepSize: 10,
                                font: { size: 11 }
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { size: 11 }
                            }
                        }
                    }
                }
            });
        }

        function actualizarTopAlumnos(alumnos) {
            const container = document.getElementById('topAlumnos');
            
            if (!alumnos || alumnos.length === 0) {
                container.innerHTML = '<p class="text-muted">No hay datos disponibles</p>';
                return;
            }
            
            const medallas = ['🥇', '🥈', '🥉'];
            let html = '';
            alumnos.forEach(function(alumno, index) {
                const medalla = index < 3 ? medallas[index] : `${index + 1}.`;
                const color = index === 0 ? '#667eea' : index === 1 ? '#2ecc71' : index === 2 ? '#f39c12' : '#7f8c8d';
                html += `
                    <div style="display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid #f0f0f0;">
                        <span style="font-weight:600; min-width:30px; font-size:16px;">${medalla}</span>
                        <span style="flex:1; font-size:14px;">${alumno.nombre} ${alumno.apellido_paterno}</span>
                        <span style="font-weight:700; color:${color}; font-size:16px;">${alumno.promedio.toFixed(1)}</span>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }

        function actualizarEstadisticas() {
            // Mostrar loading en los gráficos
            mostrarLoading();
            
            const filtros = {
                id_carrera: $('#filtroCarrera').val(),
                id_semestre: $('#filtroSemestre').val()
            };

            $.ajax({
                url: '../../ajax/estadisticas.php',
                type: 'POST',
                data: { 
                    action: 'getStats',
                    filtros: filtros
                },
                dataType: 'json',
                timeout: 10000,
                success: function(data) {
                    // Actualizar resumen
                    $('#totalEstudiantes').text(data.totalEstudiantes || 0);
                    $('#promedioGeneral').text(data.promedioGeneral ? data.promedioGeneral.toFixed(1) : '0.0');
                    $('#tasaAprobacion').text(data.tasaAprobacion ? data.tasaAprobacion.toFixed(1) + '%' : '0%');
                    $('#tasaReprobacion').text(data.tasaReprobacion ? data.tasaReprobacion.toFixed(1) + '%' : '0%');
                    
                    // Actualizar gráficos
                    actualizarGraficos(data);
                    
                    // Actualizar top alumnos
                    actualizarTopAlumnos(data.topAlumnos || []);
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar estadísticas:', error);
                    mostrarError();
                }
            });
        }

        function mostrarLoading() {
            // No hacemos nada aquí porque los gráficos se actualizan solos
        }

        function mostrarError() {
            // Mostrar mensaje de error en los gráficos
            const ctx = document.getElementById('chartRendimiento').getContext('2d');
            if (chartRendimiento) {
                chartRendimiento.destroy();
                chartRendimiento = null;
            }
            // Mostrar un gráfico vacío con mensaje
            chartRendimiento = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Sin datos'],
                    datasets: [{
                        label: 'Error',
                        data: [0],
                        backgroundColor: ['#e74c3c']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    },
                    scales: {
                        y: { beginAtZero: true, max: 10 }
                    }
                }
            });
        }

        // Evento para toggle del sidebar
        document.addEventListener('sidebarToggle', function(e) {
            const mainContent = document.getElementById('mainContent');
            if (mainContent) {
                mainContent.classList.toggle('expanded', e.detail.collapsed);
            }
            
            // Redibujar gráficos después de la animación
            setTimeout(function() {
                if (chartRendimiento) chartRendimiento.resize();
                if (chartCarreras) chartCarreras.resize();
                if (chartCalificaciones) chartCalificaciones.resize();
            }, 350);
        });

        // Redibujar gráficos al cambiar el tamaño de la ventana
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                if (chartRendimiento) chartRendimiento.resize();
                if (chartCarreras) chartCarreras.resize();
                if (chartCalificaciones) chartCalificaciones.resize();
            }, 250);
        });
    </script>
</body>
</html>