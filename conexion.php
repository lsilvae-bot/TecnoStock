<?php
$host = 'localhost';
$dbname = 'tecnostock'; // El nombre de tu base de datos
$username = 'root';     // Usuario por defecto de XAMPP
$password = '';         // Contraseña por defecto (suele estar vacía)

try {
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Configurar PDO para que muestre los errores
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>