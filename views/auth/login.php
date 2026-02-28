<?php
if (!defined('SIG_START')) {
    define('SIG_START', true);
    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/../../config/security.php';
}

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'dashboard');
    exit;
}

$baseUrl = defined('BASE_URL') ? BASE_URL : 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/sig/';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIG</title>
    
    <link rel="icon" type="image/x-icon" href="<?= $baseUrl ?>assets/img/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.min.css">
    
    <style>
        :root {
            --primary-black: #0a0a0a;
            --primary-red: #dc2626;
            --primary-white: #ffffff;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--primary-black) 0%, #1a1a1a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            border: 1px solid rgba(220, 38, 38, 0.2);
        }
        
        .login-header {
            background: var(--primary-black);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        
        .login-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, var(--primary-red), transparent);
        }
        
        .logo-container {
            width: 90px;
            height: 90px;
            background: var(--primary-black);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 4px solid var(--primary-red);
            box-shadow: 0 0 30px rgba(220, 38, 38, 0.4);
            overflow: hidden;
        }
        
        .logo-container img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            background-color: var(--primary-black);
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-floating input {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            height: 60px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-floating input:focus {
            border-color: var(--primary-red);
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-black) 0%, #374151 100%);
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-weight: 600;
            font-size: 16px;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        }
        
        .security-badges {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            opacity: 0.6;
        }
        
        .security-badges i {
            font-size: 24px;
            color: #1f2937;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .spinner-ring {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(220, 38, 38, 0.3);
            border-top-color: var(--primary-red);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-ring"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-container">
                    <img src="<?= $baseUrl ?>assets/img/logo.png" alt="SIG Logo">
                </div>
                <h3 class="text-white mb-1">Sistema de Inventario</h3>
                <small class="text-white-50">General v1.0.0</small>
            </div>
            
            <div class="login-body">
                <form id="loginForm">
                    <input type="hidden" name="csrf_token" 
                           value="<?= class_exists('Security') ? Security::generateCSRFToken() : '' ?>">
                    
                    <div class="form-floating">
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Usuario" required autocomplete="username">
                        <label for="username"><i class="fas fa-user me-2"></i>Usuario</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Contraseña" required autocomplete="current-password">
                        <label for="password"><i class="fas fa-lock me-2"></i>Contraseña</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login w-100 text-white">
                        <i class="fas fa-sign-in-alt me-2"></i>Ingresar al Sistema
                    </button>
                </form>
                
                <div class="security-badges">
                    <i class="fas fa-shield-alt" title="Conexión Segura"></i>
                    <i class="fas fa-lock" title="Datos Encriptados"></i>
                    <i class="fas fa-user-shield" title="Protección Anti-hacking"></i>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>
    
    <script>
    $(document).ready(function() {
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            const $btn = $(this).find('button[type="submit"]');
            const originalText = $btn.html();
            
            $('#loadingOverlay').fadeIn();
            $btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin me-2"></i>Verificando...');
            
            // CORREGIDO: URL directa al archivo PHP
            const loginUrl = '<?= $baseUrl ?>api/auth.php?action=login';
            
            console.log('Enviando a:', loginUrl);
            
            $.ajax({
                url: loginUrl,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    username: $('#username').val(),
                    password: $('#password').val(),
                    csrf_token: $('input[name="csrf_token"]').val()
                }),
                success: function(response) {
                    console.log('Respuesta:', response);
                    
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Bienvenido!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = response.data.redirect;
                        });
                    } else {
                        throw new Error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', status, error);
                    console.error('Response:', xhr.responseText);
                    
                    let message = 'Error de conexión';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        message = response.message || message;
                    } catch(e) {
                        message = 'Error ' + xhr.status + ': ' + error;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de acceso',
                        text: message,
                        confirmButtonColor: '#dc2626'
                    });
                },
                complete: function() {
                    $('#loadingOverlay').fadeOut();
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
    </script>
</body>
</html>