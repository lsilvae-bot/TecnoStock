<?php
require_once 'conexion.php';

$mensaje_error = '';

// 1. Obtener las categorías activas de la base de datos para el menú desplegable
try {
    $stmt_cat = $conexion->query("SELECT * FROM categorias WHERE estado = 1");
    $categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar las categorías: " . $e->getMessage());
}

// 2. Procesar el formulario cuando el usuario hace clic en "Guardar Producto"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $id_categoria = intval($_POST['id_categoria']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $stock_minimo = intval($_POST['stock_minimo']);

    // Validaciones (Reglas de negocio)
    if (empty($codigo) || empty($nombre)) {
        $mensaje_error = "El código y el nombre son obligatorios.";
    } elseif ($precio < 0 || $stock < 0 || $stock_minimo < 0) {
        $mensaje_error = "El precio, el stock y el stock mínimo no pueden ser negativos.";
    } else {
        try {
            // Verificar que el código no exista ya en la base de datos
            $sql_check = "SELECT COUNT(*) FROM productos WHERE codigo = :codigo";
            $stmt_check = $conexion->prepare($sql_check);
            $stmt_check->execute([':codigo' => $codigo]);
            
            if ($stmt_check->fetchColumn() > 0) {
                $mensaje_error = "Error: Ya existe un producto registrado con el código '$codigo'.";
            } else {
                // Insertar el nuevo producto con estado = 1 (Activo)
                $sql_insert = "INSERT INTO productos (codigo, nombre, descripcion, precio, stock, stock_minimo, id_categoria, estado) 
                               VALUES (:codigo, :nombre, :descripcion, :precio, :stock, :stock_minimo, :id_categoria, 1)";
                
                $stmt_insert = $conexion->prepare($sql_insert);
                $stmt_insert->execute([
                    ':codigo' => $codigo,
                    ':nombre' => $nombre,
                    ':descripcion' => $descripcion,
                    ':precio' => $precio,
                    ':stock' => $stock,
                    ':stock_minimo' => $stock_minimo,
                    ':id_categoria' => $id_categoria
                ]);

                // Volver al panel principal tras guardar con éxito
                header("Location: dashboard.php");
                exit;
            }
        } catch (PDOException $e) {
            $mensaje_error = "Error al registrar el producto: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Producto - TecnoStock</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .form-container { max-width: 500px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-guardar { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: #000; border: none; padding: 12px 20px; font-size: 15px; font-weight: bold; border-radius: 20px; cursor: pointer; width: 100%; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-guardar:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(67, 233, 123, 0.4); }
        .btn-volver { display: block; text-align: center; margin-top: 15px; color: #007BFF; text-decoration: none; }
        .error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>➕ Agregar Nuevo Producto</h2>

    <?php if (!empty($mensaje_error)): ?>
        <div class="error"><?php echo htmlspecialchars($mensaje_error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="codigo">Código del Producto *</label>
            <input type="text" id="codigo" name="codigo" required placeholder="Ej: TEC-001">
        </div>

        <div class="form-group">
            <label for="nombre">Nombre del Producto *</label>
            <input type="text" id="nombre" name="nombre" required placeholder="Ej: Teclado Mecánico">
        </div>

        <div class="form-group">
            <label for="id_categoria">Categoría *</label>
            <select id="id_categoria" name="id_categoria" required>
                <option value="">-- Selecciona una categoría --</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id_categoria']; ?>">
                        <?php echo htmlspecialchars($cat['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="3" placeholder="Detalles del producto..."></textarea>
        </div>

        <div class="form-group">
            <label for="precio">Precio ($) *</label>
            <input type="number" id="precio" name="precio" step="0.01" min="0" required>
        </div>

        <div class="form-group">
            <label for="stock">Stock Inicial *</label>
            <input type="number" id="stock" name="stock" min="0" required>
        </div>

        <div class="form-group">
            <label for="stock_minimo">Stock Mínimo de Alerta *</label>
            <input type="number" id="stock_minimo" name="stock_minimo" min="0" value="5" required>
        </div>

        <button type="submit" class="btn-guardar">Guardar Producto</button>
    </form>

    <a href="dashboard.php" class="btn-volver">← Cancelar y volver al inventario</a>
</div>

</body>
</html>