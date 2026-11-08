<?php
// Incluimos la conexión a la base de datos
require 'conexion.php';

// Hacemos la consulta para obtener todos los productos
try {
    $sql = "SELECT * FROM productos";
    $stmt = $conexion->query($sql);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener los productos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TecnoStock</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background-color: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #007BFF; color: white; }
        .btn { padding: 10px 15px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px; display: inline-block; }
        .btn:hover { background-color: #218838; }
        h2 { color: #333; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Inventario de TecnoStock</h2>
        <a href="agregar.php" class="btn">Agregar Nuevo Producto</a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($productos) > 0): ?>
                    <?php foreach ($productos as $producto): ?>
                        <tr>
                            <!-- Asegúrate de que estos nombres coincidan con las columnas de tu base de datos -->
                            <td><?php echo $producto['id']; ?></td>
                            <td><?php echo $producto['nombre']; ?></td>
                            <td><?php echo $producto['descripcion']; ?></td>
                            <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                            <td><?php echo $producto['stock']; ?></td>
                            <td>
                                <a href="#">Editar</a> | 
                                <a href="#">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No hay productos en el inventario.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>