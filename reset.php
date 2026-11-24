<?php
require_once 'conexion.php';

// Encriptamos la clave '123456' usando el sistema de tu propio servidor
$clave_encriptada = password_hash('123456', PASSWORD_DEFAULT);

try {
    // 1. Borramos el usuario admin anterior si existe
    $conexion->query("DELETE FROM usuarios WHERE usuario = 'admin'");

    // 2. Insertamos el usuario con la contraseña generada correctamente
    $stmt = $conexion->prepare("INSERT INTO usuarios (usuario, password, estado) VALUES ('admin', :clave, 1)");
    $stmt->execute([':clave' => $clave_encriptada]);

    // 3. Redirigir directamente al login sin mostrar mensajes
    header("Location: login.php");
    exit;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>