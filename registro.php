<?php
session_start();
require_once 'conexion.php';

// Si el usuario ya está logueado, lo mandamos al inventario
if (isset($_SESSION['id_usuario'])) {
    header("Location: dashboard.php");
    exit;
}

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];

    if (empty($usuario) || empty($password)) {
        $mensaje = "Por favor, completa todos los campos.";
        $tipo_mensaje = "error";
    } else {
        try {
            // 1. Verificar que el nombre de usuario no exista ya en la base de datos
            $stmt_check = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario");
            $stmt_check->execute([':usuario' => $usuario]);
            
            if ($stmt_check->fetchColumn() > 0) {
                $mensaje = "Error: Este nombre de usuario ya está ocupado.";
                $tipo_mensaje = "error";
            } else {
                // 2. Encriptar la contraseña y guardar el usuario nuevo
                $clave_encriptada = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt_insert = $conexion->prepare("INSERT INTO usuarios (usuario, password, estado) VALUES (:usuario, :clave, 1)");
                $stmt_insert->execute([
                    ':usuario' => $usuario,
                    ':clave' => $clave_encriptada
                ]);
                
                $mensaje = "¡Cuenta creada con éxito! Ya puedes iniciar sesión.";
                $tipo_mensaje = "exito";
            }
        } catch (PDOException $e) {
            $mensaje = "Error al registrar: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - TecnoStock</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 360px; }
        h2 { text-align: center; color: #333; margin-top: 0; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-registro { width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .btn-registro:hover { background-color: #218838; }
        .mensaje { padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .exito { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .enlace { display: block; text-align: center; margin-top: 15px; color: #007BFF; text-decoration: none; font-size: 14px; }
        .enlace:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>📝 Crear Cuenta</h2>
    
    <?php if (!empty($mensaje)): ?>
        <div class="mensaje <?php echo $tipo_mensaje; ?>"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="usuario">Nuevo Usuario</label>
            <input type="text" id="usuario" name="usuario" required autofocus>
        </div>
        
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn-registro">Registrarse</button>
    </form>
    
    <a href="login.php" class="enlace">¿Ya tienes cuenta? Inicia sesión aquí</a>
</div>

</body>
</html>