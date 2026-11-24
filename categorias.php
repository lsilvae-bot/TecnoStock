<?php
require_once 'conexion.php';

$mensaje = '';
$tipo_mensaje = '';

// 1. Procesar el formulario cuando se agrega una nueva categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_categoria'])) {
    $nombre = trim($_POST['nombre_categoria']);

    if (empty($nombre)) {
        $mensaje = "El nombre de la categoría no puede estar vacío.";
        $tipo_mensaje = "error";
    } else {
        try {
            // Verificar que no exista otra categoría con el mismo nombre
            $stmt_check = $conexion->prepare("SELECT COUNT(*) FROM categorias WHERE nombre = :nombre");
            $stmt_check->execute([':nombre' => $nombre]);
            
            if ($stmt_check->fetchColumn() > 0) {
                $mensaje = "Error: Ya existe una categoría con ese nombre.";
                $tipo_mensaje = "error";
            } else {
                // Insertar la nueva categoría
                $stmt_insert = $conexion->prepare("INSERT INTO categorias (nombre, estado) VALUES (:nombre, 1)");
                $stmt_insert->execute([':nombre' => $nombre]);
                
                $mensaje = "¡Categoría '$nombre' agregada con éxito!";
                $tipo_mensaje = "exito";
            }
        } catch (PDOException $e) {
            $mensaje = "Error al guardar: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    }
}

// 2. Obtener la lista de categorías activas para mostrarlas en la tabla
try {
    $stmt_cat = $conexion->query("SELECT * FROM categorias WHERE estado = 1 ORDER BY nombre ASC");
    $categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar categorías: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Categorías - TecnoStock</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-top: 0; }
        .form-group { display: flex; gap: 10px; margin-bottom: 20px; }
        input[type="text"] { flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-guardar { background-color: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-guardar:hover { opacity: 0.9; }
        .btn-volver { display: block; text-align: center; margin-top: 20px; color: #007BFF; text-decoration: none; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #007BFF; color: white; }
        
        .mensaje { padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .exito { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>

<div class="container">
    <h2>📂 Administrar Categorías</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="mensaje <?php echo $tipo_mensaje; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <!-- Formulario para agregar -->
    <form method="POST" action="">
        <div class="form-group">
            <input type="text" name="nombre_categoria" placeholder="Nombre de la nueva categoría..." required>
            <button type="submit" class="btn-guardar">➕ Agregar</button>
        </div>
    </form>

    <!-- Tabla que lista las categorías -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre de Categoría</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($categorias) > 0): ?>
                <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td><?php echo $cat['id_categoria']; ?></td>
                        <td><?php echo htmlspecialchars($cat['nombre']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2" style="text-align: center;">No hay categorías registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn-volver">← Volver al inventario</a>
</div>

</body>
</html>