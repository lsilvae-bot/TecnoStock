<?php
session_start();
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    try {
        $sql = "SELECT * FROM usuarios WHERE usuario = :usuario";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['usuario' => $usuario]);
        $user = $stmt->fetch();

        if ($user && $password === $user['password']) {
            $_SESSION['usuario'] = $user['usuario'];
            header("Location: dashboard.php");
            exit;
        } else {
            echo "Error: Usuario o contraseña incorrectos.";
        }
    } catch (PDOException $e) {
        echo "Error en la base de datos: " . $e->getMessage();
    }
}
?>