<?php
session_start();
require 'conexion.php';

if (isset($_SESSION['id_usuario'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];

    if (empty($usuario) || empty($password)) {
        $error = "Por favor, completa todos los campos.";
    } else {
        try {
            $stmt = $conexion->prepare("SELECT id_usuario, usuario, password FROM usuarios WHERE usuario = :usuario AND estado = 1");
            $stmt->execute([':usuario' => $usuario]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['id_usuario'] = $user['id_usuario'];
                $_SESSION['usuario'] = $user['usuario'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Usuario o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - TecnoStock</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-image: url('image_57d265.png'); /*[cite: 2]*/
            background-size: cover; 
            background-position: center; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .login-box { 
            background-color: rgba(255, 255, 255, 0.90); 
            backdrop-filter: blur(5px);
            padding: 40px 30px; 
            border-radius: 12px; 
            box-shadow: 0 8px 25px rgba(0,0,0,0.3); 
            width: 100%; 
            max-width: 360px; 
        }
        h2 { text-align: center; color: #2c3e50; margin-top: 0; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input[type="text"], input[type="password"] { 
            width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; 
        }
        .btn-login { 
            width: 100%; padding: 12px; background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);
            color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; 
        }
        .error { background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .enlace-registro { display: block; text-align: center; margin-top: 20px; color: #0072ff; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>🔐 Acceso TecnoStock</h2>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Usuario</label>
            <input type="text" name="usuario" required autofocus>
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn-login">Ingresar al Sistema</button>
    </form>
    <a href="registro.php" class="enlace-registro">¿No tienes cuenta? Regístrate aquí</a>
</div>

</body>
</html>