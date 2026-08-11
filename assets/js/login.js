// assets/js/login.js
$(function() {
    // Verificar si ya hay sesión activa
    verificarSesion();
    
    // Evento submit del formulario
    $("#frmLogin").on("submit", function(e) {
        e.preventDefault();
        login();
    });
    
    // Enter key en el campo de contraseña
    $("#password").on("keypress", function(e) {
        if (e.which === 13) {
            e.preventDefault();
            login();
        }
    });
    
    // Auto-focus en el campo de usuario
    $("#usuario").focus();
});

function verificarSesion() {
    $.ajax({
        url: '../ajax/get_user.php',
        type: 'POST',
        dataType: 'json',
        timeout: 5000,
        success: function(response) {
            if (response && response.id_usuario) {
                // Guardar en sessionStorage
                sessionStorage.setItem('userData', JSON.stringify(response));
                
                const redirects = {
                    1: '../admin/dashboard.php',
                    2: '../coordinator/dashboard.php',
                    3: '../teacher/dashboard.php'
                };
                const destino = redirects[response.id_rol] || '../index.php';
                window.location.href = destino;
            }
        },
        error: function(xhr, status, error) {
            // Si hay error, no hacemos nada, solo continuamos con el login
            console.log('No hay sesión activa o error al verificar:', error);
            // Limpiar cualquier dato de sesión en el frontend
            sessionStorage.removeItem('userData');
        }
    });
}

function login() {
    const usuario = $("#usuario").val().trim();
    const password = $("#password").val();
    
    $("#loginError").removeClass("show").text("");
    
    if (usuario === "") {
        showError("Por favor, ingresa tu usuario.");
        $("#usuario").focus();
        return;
    }
    
    if (password === "") {
        showError("Por favor, ingresa tu contraseña.");
        $("#password").focus();
        return;
    }
    
    const $btnLogin = $("#btnLogin");
    const textoOriginal = $btnLogin.html();
    
    $btnLogin
        .prop("disabled", true)
        .html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Validando...');
    
    $.ajax({
        url: '../ajax/users.php',
        type: "POST",
        dataType: "json",
        data: {
            action: "login",
            usuario: usuario,
            password: password
        },
        timeout: 10000,
        success: function(response) {
            if (response.success) {
                // Guardar datos del usuario en sessionStorage
                if (response.user) {
                    sessionStorage.setItem('userData', JSON.stringify(response.user));
                }
                
                const redirects = {
                    1: '../admin/dashboard.php',
                    2: '../coordinator/dashboard.php',
                    3: '../teacher/dashboard.php'
                };
                
                const destino = redirects[response.rol] || '../index.php';
                
                setTimeout(function() {
                    window.location.href = destino;
                }, 300);
            } else {
                showError(response.message || "Usuario o contraseña incorrectos.");
                $("#password").val("").focus();
                // Limpiar cualquier dato de sesión
                sessionStorage.removeItem('userData');
            }
        },
        error: function(xhr, status, error) {
            console.error("Error en login:", {
                status: status,
                error: error,
                response: xhr.responseText
            });
            
            let mensaje = "Error al conectar con el servidor. ";
            if (status === "timeout") {
                mensaje += "La conexión ha tardado demasiado. Intenta nuevamente.";
            } else if (status === "parsererror") {
                mensaje += "Error en la respuesta del servidor.";
            } else {
                mensaje += "Intenta nuevamente más tarde.";
            }
            
            showError(mensaje);
            sessionStorage.removeItem('userData');
        },
        complete: function() {
            $btnLogin
                .prop("disabled", false)
                .html(textoOriginal);
        }
    });
}

function showError(message) {
    const $error = $("#loginError");
    $error.text(message).addClass("show");
    
    setTimeout(function() {
        $error.removeClass("show");
    }, 5000);
}