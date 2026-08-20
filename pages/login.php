<?php
// pages/login.php
session_start();

// Si ya tiene sesión, redirigir
if (isset($_SESSION['usuario'])) {
    $redirects = [
        1 => '../admin/dashboard.php',
        2 => '../coordinator/dashboard.php',
        3 => '../maestro/dashboard.php'
    ];
    header('Location: ' . ($redirects[$_SESSION['usuario']['id_rol']] ?? '../index.php'));
    exit;
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - SIOUNAM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <style>
        
        :root {
            --primary-blue: #0056b3;
            --secondary-gold: #d4af37;
        }

        
        .login-body {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('../assets/img/fondo.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: transparent;
            border-radius: 24px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 40px rgba(0,0,0,0.2) !important;
        }

        .card .col-md-6:first-child {
            background: rgba(0, 0, 0, 0.137) !important;
            backdrop-filter: blur(10px);
        }

    
        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            font-size: 1rem;
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.25);
            outline: none;
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        /* Botón ACCEDER */
        .btn-primary {
            background: linear-gradient(135deg, #0056b3 0%, #003d80 100%);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 86, 179, 0.4);
            background: linear-gradient(135deg, #003d80 0%, #002b66 100%);
        }

        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

    
        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            border-radius: 12px;
            font-size: 0.9rem;
        }

        /* Imagen del lado derecho  */
        .col-md-6.p-0 img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        
        
        /* Toggle de contraseña */
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            font-size: 16px;
            padding: 5px;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #ffffff;
        }

        .login-error {
            color: #dc2626;
            font-size: 14px;
            margin-top: 15px;
            text-align: center;
            display: none;
            padding: 10px 15px;
            background: #fef2f2;
            border-radius: 12px;
            border: 1px solid #fecaca;
        }

        .login-error.show {
            display: block;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

    
        .spinner-border {
            width: 18px;
            height: 18px;
            border-width: 2px;
        }

        
        @media (max-width: 768px) {
            .col-md-6.d-none.d-md-block {
                display: none !important;
            }
            .card {
                margin: 1rem;
            }
            .p-4 {
                padding: 1.5rem !important;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
                margin: 15px;
            }
        }

        
        footer {
            width: 100%;
            text-align: center;
            padding: 15px 0;
            background: transparent;
            color: #6c757d;
            font-size: 14px;
            position: relative;
            bottom: 0;
            left: 0;
            margin-top: 20px;
        }
    </style>
</head>
<body class="login-body">

    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        
        <div class="card shadow-lg border-0 overflow-hidden" style="max-width: 1400px; width: 95%;">
            <div class="row g-0">
                
                
                <div class="col-md-6 d-flex align-items-center justify-content-center p-4 p-md-5">
                    <div class="w-100" style="max-width: 320px;">
                     
                        <h1 class="fw-bold mb-2" style="color: #ffffff;">Bienvenido a SIOUNAM</h1>
                        <h5 class="fw-bold mb-2" style="color: #ffffff;">Sistema Informativo Oparin.</h5>
                        <p class="text mb-4" style="color: rgba(255,255,255,0.8);">Ingresa tu contraseña</p>

                        <form id="frmLogin" novalidate>
                           
                            <div class="mb-3">
                                <input 
                                    type="text" 
                                    name="usuario" 
                                    class="form-control form-control-lg" 
                                    id="usuario"
                                    placeholder="Nombre de usuario" 
                                    required
                                    autocomplete="username"
                                    autofocus>
                            </div>
                            
                            
                            <div class="mb-4 position-relative">
                                <input 
                                    type="password" 
                                    name="password" 
                                    class="form-control form-control-lg" 
                                    id="password"
                                    placeholder="Contraseña" 
                                    required
                                    autocomplete="current-password">
                                <button type="button" class="password-toggle" id="togglePassword" title="Mostrar/Ocultar contraseña">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            
                            
                            <button type="submit" class="btn btn-primary w-100 btn-lg" id="btnLogin">
                                ACCEDER
                            </button>
                            
                            <div id="loginError" class="login-error"></div>
                        </form>
                    </div>
                </div>

                <div class="col-md-6 d-none d-md-block p-0">
                    <img src="../assets/img/sisomosunam.png" alt="Logo 50 años OPARIN" class="img-fluid w-100 h-100" style="object-fit: cover; min-height: 550px;">
                </div>
            </div>
        </div>
    </div>

    <!-- Footer  -->
    <footer>
        &copy; 2026 SIOUNAM - Todos los derechos reservados
    </footer>

    <script src="../assets/js/login.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>