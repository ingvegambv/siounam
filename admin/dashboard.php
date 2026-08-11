<?php
// admin/dashboard.php
require_once 'includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIOUNAM</title>
    <link rel="stylesheet" href="../assets/css/components.css">
    <script src="../assets/components/sidebar-component.js" defer></script>
    <script src="../assets/components/header-component.js" defer></script>
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
        
        .chart-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .empty-chart-message {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 200px;
            color: #7f8c8d;
            text-align: center;
        }
        
        .empty-chart-message i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 10px;
        }
        
        .empty-chart-message p {
            margin: 0;
            font-size: 14px;
        }
        
        .empty-chart-message small {
            font-size: 12px;
            color: #b0b8c4;
        }
        
        @media (max-width: 768px) {
            .chart-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="app-layout">
    <sidebar-component base-path="../"></sidebar-component>
        <div class="main-content" id="mainContent">
            <header-component title="Dashboard" icon="th-large">
                <div slot="actions">
                    <button class="btn btn-primary btn-sm" onclick="actualizarDashboard()">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </header-component>

            <!-- Stats Cards -->
            <div class="card-grid">
                <div class="stat-card fade-in-up" style="animation-delay: 0.1s">
                    <div class="stat-icon text-primary"><i class="fas fa-users"></i></div>
                    <div class="stat-number" id="totalUsuarios">0</div>
                    <div class="stat-label">Total Usuarios</div>
                </div>
                <div class="stat-card fade-in-up" style="animation-delay: 0.2s">
                    <div class="stat-icon text-success"><i class="fas fa-user-graduate"></i></div>
                    <div class="stat-number" id="totalAlumnos">0</div>
                    <div class="stat-label">Total Alumnos</div>
                </div>
                <div class="stat-card fade-in-up" style="animation-delay: 0.3s">
                    <div class="stat-icon text-warning"><i class="fas fa-book"></i></div>
                    <div class="stat-number" id="totalMaterias">0</div>
                    <div class="stat-label">Materias Activas</div>
                </div>
                <div class="stat-card fade-in-up" style="animation-delay: 0.4s">
                    <div class="stat-icon text-danger"><i class="fas fa-users-cog"></i></div>
                    <div class="stat-number" id="totalGrupos">0</div>
                    <div class="stat-label">Grupos Activos</div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="chart-grid">
                <div class="chart-wrapper">
                    <h5><i class="fas fa-chart-line text-primary me-2"></i>Evolución de Alumnos</h5>
                    <div class="chart-container">
                        <canvas id="chartAlumnos"></canvas>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <h5><i class="fas fa-chart-pie text-primary me-2"></i>Distribución por Carrera</h5>
                    <div class="chart-container">
                        <canvas id="chartCarreras"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="stat-card">
                <h5><i class="fas fa-clock text-primary me-2"></i>Actividad Reciente</h5>
                <div id="actividadReciente" style="margin-top:10px;">
                    <p class="text-muted">Cargando actividad...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let chartAlumnos = null;
        let chartCarreras = null;

        $(document).ready(function() {
            cargarDatosUsuario();
            cargarDashboardData();
        });

        function cargarDatosUsuario() {
            $.ajax({
                url: '../ajax/get_user.php',
                type: 'POST',
                dataType: 'json',
                success: function(user) {
                    const sidebar = document.getElementById('appSidebar');
                    if (sidebar && sidebar.setUserData) {
                        sidebar.setUserData(user);
                    }
                }
            });
        }

        function cargarDashboardData() {
            $.ajax({
                url: '../ajax/stats.php',
                type: 'POST',
                dataType: 'json',
                success: function(data) {
                    // Actualizar tarjetas
                    $('#totalUsuarios').text(data.totalUsuarios || 0);
                    $('#totalAlumnos').text(data.totalAlumnos || 0);
                    $('#totalMaterias').text(data.totalMaterias || 0);
                    $('#totalGrupos').text(data.totalGrupos || 0);
                    
                    // Crear gráficos
                    crearGraficoAlumnos(data.evolucion || []);
                    crearGraficoCarreras(data.carreras || []);
                    
                    // Actividad reciente
                    cargarActividadReciente();
                },
                error: function() {
                    console.error('Error al cargar datos del dashboard');
                }
            });
        }

        function crearGraficoAlumnos(evolucionData) {
            const ctx = document.getElementById('chartAlumnos').getContext('2d');
            
            if (chartAlumnos) {
                chartAlumnos.destroy();
                chartAlumnos = null;
            }
            
            const labels = evolucionData.map(item => item.label);
            const values = evolucionData.map(item => item.value);
            const tieneDatos = values.some(v => v > 0);
            
            chartAlumnos = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Alumnos',
                        data: values,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#667eea',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
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
                                    return `Alumnos: ${context.parsed.y}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: Math.max(1, Math.ceil(Math.max(...values, 10) / 10)),
                                font: { size: 11 }
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        function crearGraficoCarreras(carrerasData) {
            const ctx = document.getElementById('chartCarreras').getContext('2d');
            
            if (chartCarreras) {
                chartCarreras.destroy();
                chartCarreras = null;
            }
            
            const tieneDatos = carrerasData.some(item => item.value > 0);
            
            // Si no hay datos, mostrar un gráfico vacío
            if (!tieneDatos || carrerasData.length === 0) {
                carrerasData = [{ label: 'Sin datos', value: 1 }];
            }
            
            const colors = ['#667eea', '#2ecc71', '#f39c12', '#e74c3c', '#3498db', '#9b59b6', '#1abc9c', '#e67e22', '#2c3e50', '#95a5a6'];
            
            chartCarreras = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: carrerasData.map(item => item.label),
                    datasets: [{
                        data: carrerasData.map(item => item.value),
                        backgroundColor: colors.slice(0, carrerasData.length),
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
                                    const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                    return `${context.label}: ${context.parsed} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        function cargarActividadReciente() {
            $.ajax({
                url: '../ajax/activity.php',
                type: 'POST',
                data: { action: 'getRecentActivity' },
                dataType: 'json',
                success: function(data) {
                    const container = $('#actividadReciente');
                    if (data && data.length > 0) {
                        let html = '';
                        data.forEach(function(item) {
                            html += `
                                <div style="display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #f0f0f0;">
                                    <div style="width:8px; height:8px; border-radius:50%; background: #667eea;"></div>
                                    <span style="font-size:14px; color:#2c3e50;">${item}</span>
                                </div>
                            `;
                        });
                        container.html(html);
                    } else {
                        container.html(`
                            <div style="text-align:center; padding:20px; color:#7f8c8d;">
                                <i class="fas fa-clock" style="font-size:24px; display:block; margin-bottom:10px;"></i>
                                <p>No hay actividad reciente</p>
                            </div>
                        `);
                    }
                }
            });
        }

        function actualizarDashboard() {
            cargarDashboardData();
            const btn = $('.btn-primary:contains("Actualizar")');
            const originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> Actualizando...').prop('disabled', true);
            setTimeout(function() {
                btn.html(originalText).prop('disabled', false);
            }, 1000);
        }

        // Evento para toggle del sidebar
        document.addEventListener('sidebarToggle', function(e) {
            const mainContent = document.getElementById('mainContent');
            if (mainContent) {
                mainContent.classList.toggle('expanded', e.detail.collapsed);
            }
            setTimeout(function() {
                if (chartAlumnos) chartAlumnos.resize();
                if (chartCarreras) chartCarreras.resize();
            }, 350);
        });

        // Redibujar gráficos al cambiar el tamaño
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                if (chartAlumnos) chartAlumnos.resize();
                if (chartCarreras) chartCarreras.resize();
            }, 250);
        });
    </script>
</body>
</html>