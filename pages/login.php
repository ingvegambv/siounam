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

// Agregar headers para evitar caché
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
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            padding: 45px 40px;
            width: 100%;
            max-width: 420px;
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header .logo {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 10px;
            display: block;
        }
        
        .login-header h1 {
            color: #1a1a2e;
            font-weight: 700;
            font-size: 28px;
            margin: 0;
        }
        
        .login-header h1 span {
            color: #667eea;
        }
        
        .login-header .subtitle {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .form-floating {
            margin-bottom: 15px;
            position: relative;
        }
        
        .form-floating input {
            border-radius: 12px;
            border: 2px solid #e1e5eb;
            transition: all 0.3s ease;
            height: 56px;
            padding-right: 45px;
        }
        
        .form-floating input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #95a5a6;
            cursor: pointer;
            font-size: 16px;
            padding: 5px;
            z-index: 10;
        }
        
        .password-toggle:hover {
            color: #667eea;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 10px;
            height: 56px;
            position: relative;
        }
        
        .btn-login:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .login-error {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 12px;
            text-align: center;
            display: none;
            padding: 10px 15px;
            background: #fdf2f2;
            border-radius: 8px;
            border: 1px solid #fad2d2;
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
        
        .login-footer {
            text-align: center;
            margin-top: 25px;
            color: #b0b8c4;
            font-size: 13px;
        }
        
        .spinner-border {
            width: 18px;
            height: 18px;
            border-width: 2px;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
                margin: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <span class="logo"><i class="fas fa-graduation-cap"></i></span>
            <h1>SIO<span>UNAM</span></h1>
            <p class="subtitle">Sistema Integral de la UNAM</p>
        </div>
        
        <form id="frmLogin" novalidate>
            <div class="form-floating">
                <input 
                    type="text" 
                    class="form-control" 
                    id="usuario" 
                    name="usuario" 
                    placeholder="Usuario"
                    required
                    autocomplete="username"
                    autofocus>
                <label for="usuario"><i class="fas fa-user me-2"></i>Usuario</label>
            </div>
            
            <div class="form-floating">
                <input 
                    type="password" 
                    class="form-control" 
                    id="password" 
                    name="password" 
                    placeholder="Contraseña"
                    required
                    autocomplete="current-password">
                <label for="password"><i class="fas fa-lock me-2"></i>Contraseña</label>
                <button type="button" class="password-toggle" id="togglePassword" title="Mostrar/Ocultar contraseña">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            
            <button type="submit" class="btn btn-login" id="btnLogin">
                <i class="fas fa-sign-in-alt me-2"></i>Iniciar sesión
            </button>
            
            <div id="loginError" class="login-error"></div>
        </form>
        
        <div class="login-footer">
            &copy; 2026 SIOUNAM - Todos los derechos reservados
        </div>
    </div>
    
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