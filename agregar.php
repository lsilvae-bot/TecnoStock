<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];

    try {
        $sql = "INSERT INTO productos (nombre, descripcion, precio, stock) VALUES (:nombre, :descripcion, :precio, :stock)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'stock' => $stock
        ]);
        header("Location: dashboard.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error al guardar el producto: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Producto</title>
</head>
<body>
    <h2>Agregar Nuevo Producto</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form action="agregar.php" method="POST">
        <label>Nombre:</label><br>
        <input type="text" name="nombre" required><br><br>
        
        <label>Descripción:</label><br>
        <textarea name="descripcion" required></textarea><br><br>
        
        <label>Precio:</label><br>
        <input type="number" step="0.01" name="precio" required><br><br>
        
        <label>Stock:</label><br>
        <input type="number" name="stock" required><br><br>
        
        <button type="submit">Guardar Producto</button>
        <a href="dashboard.php">Cancelar</a>
    </form>
</body>
</html>