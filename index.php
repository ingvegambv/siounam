<?php
// index.php - Landing page pública
session_start();

// Si ya tiene sesión, redirigir al dashboard
if (isset($_SESSION['usuario'])) {
    $rol = $_SESSION['usuario']['id_rol'];
    $redirects = [
        1 => 'admin/dashboard.php',
        2 => 'coordinator/dashboard.php',
        3 => 'teacher/dashboard.php'
    ];
    header('Location: ' . ($redirects[$rol] ?? 'pages/login.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIOUNAM - Sistema Integral UNAM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .landing-container {
            text-align: center;
            color: white;
            padding: 40px;
            animation: fadeInUp 0.8s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            font-size: 80px;
            margin-bottom: 20px;
            display: block;
        }
        
        .landing-container h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .landing-container .subtitle {
            font-size: 20px;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        
        .btn-custom {
            padding: 15px 40px;
            border-radius: 50px;
            background: white;
            color: #667eea;
            font-weight: 600;
            font-size: 18px;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            color: #667eea;
        }
        
        .features {
            margin-top: 50px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .feature-item {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        
        .feature-item i {
            font-size: 30px;
            margin-bottom: 10px;
            display: block;
        }
        
        .feature-item h5 {
            font-weight: 600;
        }
        
        .feature-item p {
            font-size: 14px;
            opacity: 0.8;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .landing-container h1 {
                font-size: 32px;
            }
            
            .features {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <span class="logo"><i class="fas fa-graduation-cap"></i></span>
        <h1>SIOUNAM</h1>
        <p class="subtitle">Sistema Integral de la Universidad Nacional Autónoma de México</p>
        
        <a href="pages/login.php" class="btn-custom">
            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
        </a>
        
        <div class="features">
            <div class="feature-item">
                <i class="fas fa-users"></i>
                <h5>Gestión de Usuarios</h5>
                <p>Administra todos los perfiles del sistema</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-chalkboard-teacher"></i>
                <h5>Asignación de Materias</h5>
                <p>Organiza grupos y materias por semestre</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-chart-bar"></i>
                <h5>Estadísticas</h5>
                <p>Visualiza el rendimiento académico</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-user-graduate"></i>
                <h5>Gestión de Alumnos</h5>
                <p>Controla el progreso de los estudiantes</p>
            </div>
        </div>
        
        <div style="margin-top: 40px; opacity: 0.6; font-size: 14px;">
            &copy; 2026 SIOUNAM - Todos los derechos reservados
        </div>
    </div>
</body>
</html>