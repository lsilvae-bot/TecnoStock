<?php
require 'conexion.php';
$hash = password_hash('123456', PASSWORD_DEFAULT);
$conexion->query("DELETE FROM usuarios WHERE usuario = 'admin'");
$conexion->query("INSERT INTO usuarios (usuario, password, estado) VALUES ('admin', '$hash', 1)");
echo "<h1>¡Usuario maestro creado!</h1><p>Usuario: <b>admin</b> <br>Clave: <b>123456</b></p>";
?>