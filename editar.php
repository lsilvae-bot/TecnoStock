<?php
require_once 'conexion.php';

$mensaje_error = '';

// 1. Verificar que se recibió un ID válido por la URL
if (!isset($_GET['id']) OR empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id = $_GET['id'];

// 2. Procesar el formulario cuando se envían los cambios (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $stock_minimo = intval($_POST['stock_minimo']);
    $id_categoria = intval($_POST['id_categoria']);

    // Validaciones en el servidor (Reglas de negocio)
    if (empty($codigo) OR empty($nombre)) {
        $mensaje_error = "El código y el nombre son obligatorios.";
    } elseif ($precio < 0 OR $stock_minimo < 0) {
        $mensaje_error = "El precio y el stock mínimo no pueden ser negativos.";
    } else {
        try {
            // Verificar que el código no esté repetido en OTRO producto
            $sql_check = "SELECT COUNT(*) FROM productos WHERE codigo = :codigo AND id != :id";
            $stmt_check = $conexion->prepare($sql_check);
            $stmt_check->execute([':codigo' => $codigo, ':id' => $id]);
            
            if ($stmt_check->fetchColumn() > 0) {
                $mensaje_error = "Error: Ya existe otro producto registrado con ese código.";
            } else {
                // Actualizar los datos permitidos
                $sql_update = "UPDATE productos 
                               SET codigo = :codigo, 
                                   nombre = :nombre, 
                                   descripcion = :descripcion, 
                                   precio = :precio, 
                                   stock_minimo = :stock_minimo, 
                                   id_categoria = :id_categoria 
                               WHERE id = :id";
                
                $stmt_update = $conexion->prepare($sql_update);
                $stmt_update->execute([
                    ':codigo' => $codigo,
                    ':nombre' => $nombre,
                    ':descripcion' => $descripcion,
                    ':precio' => $precio,
                    ':stock_minimo' => $stock_minimo,
                    ':id_categoria' => $id_categoria,
                    ':id' => $id
                ]);

                header("Location: dashboard.php");
                exit;
            }
        } catch (PDOException $e) {
            $mensaje_error = "Error al actualizar: " . $e->getMessage();
        }
    }
}

// 3. Obtener los datos actuales del producto para rellenar el formulario
try {
    $stmt = $conexion->prepare("SELECT * FROM productos WHERE id = :id AND estado = 1");
    $stmt->execute([':id' => $id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        die("Producto no encontrado o desactivado.");
    }

    // Obtener las categorías activas para el menú desplegable
    $stmt_cat = $conexion->query("SELECT * FROM categorias WHERE estado = 1");
    $categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - TecnoStock</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .form-container { max-width: 500px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        input[readonly] { background-color: #e9ecef; cursor: not-allowed; }
        .btn-guardar { background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); color: white; border: none; padding: 12px 20px; font-size: 15px; font-weight: bold; border-radius: 20px; cursor: pointer; width: 100%; text-shadow: 1px 1px 2px rgba(0,0,0,0.2); }
        .btn-guardar:hover { opacity: 0.9; }
        .btn-volver { display: block; text-align: center; margin-top: 15px; color: #007BFF; text-decoration: none; }
        .error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #f5c6cb; }
        small { color: #666; font-size: 12px; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>✏️ Editar Producto</h2>

    <?php if (!empty($mensaje_error)): ?>
        <div class="error"><?php echo htmlspecialchars($mensaje_error); ?></div>
    <?php endif; ?>

    <!-- AQUÍ ESTÁ EL CAMBIO CLAVE: Se asegura de enviar el ID en la URL al guardar -->
    <form method="POST" action="editar.php?id=<?php echo htmlspecialchars($id); ?>">
        
        <div class="form-group">
            <label for="codigo">Código del Producto *</label>
            <input type="text" id="codigo" name="codigo" value="<?php echo htmlspecialchars($producto['codigo'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="nombre">Nombre *</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
        </div>

        <div class="form-group">
            <label for="id_categoria">Categoría *</label>
            <select id="id_categoria" name="id_categoria" required>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($cat['id_categoria'] == $producto['id_categoria']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="precio">Precio ($) *</label>
            <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?php echo htmlspecialchars($producto['precio']); ?>" required>
        </div>

        <div class="form-group">
            <label for="stock">Stock Actual (Solo lectura)</label>
            <input type="number" id="stock" value="<?php echo htmlspecialchars($producto['stock']); ?>" readonly>
            <small>El stock se modifica registrando entradas o salidas en el módulo de movimientos.</small>
        </div>

        <div class="form-group">
            <label for="stock_minimo">Stock Mínimo de Alerta *</label>
            <input type="number" id="stock_minimo" name="stock_minimo" min="0" value="<?php echo htmlspecialchars($producto['stock_minimo']); ?>" required>
        </div>

        <button type="submit" class="btn-guardar">Guardar Cambios</button>
    </form>

    <a href="dashboard.php" class="btn-volver">← Volver al inventario</a>
</div>

</body>
</html>