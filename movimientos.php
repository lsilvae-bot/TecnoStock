<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}
require 'conexion.php';

$mensaje = '';
$tipo_mensaje = '';

// Obtener lista de productos activos para el menú desplegable
try {
    $stmt_prod = $conexion->query("SELECT id, codigo, nombre, stock FROM productos WHERE estado = 1");
    $productos = $stmt_prod->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar productos: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = $_POST['id_producto'];
    $tipo_movimiento = $_POST['tipo_movimiento']; // 'entrada' o 'salida'
    $cantidad = (int)$_POST['cantidad'];

    if (empty($id_producto) || empty($tipo_movimiento) || $cantidad <= 0) {
        $mensaje = "Por favor, completa todos los campos y asegúrate de que la cantidad sea mayor a cero.";
        $tipo_mensaje = "error";
    } else {
        try {
            // 1. INICIAMOS LA TRANSACCIÓN
            $conexion->beginTransaction();

            // 2. Consultamos el stock actual del producto
            $stmt_stock = $conexion->prepare("SELECT stock FROM productos WHERE id = :id FOR UPDATE");
            $stmt_stock->execute([':id' => $id_producto]);
            $producto = $stmt_stock->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                throw new Exception("El producto seleccionado no existe.");
            }

            $stock_actual = $producto['stock'];

            // 3. REGLA DE NEGOCIO: Validar que no quede stock negativo
            if ($tipo_movimiento === 'salida') {
                if ($cantidad > $stock_actual) {
                    throw new Exception("Error: No puedes retirar $cantidad unidades. Solo hay $stock_actual en stock.");
                }
                $nuevo_stock = $stock_actual - $cantidad;
            } else {
                $nuevo_stock = $stock_actual + $cantidad;
            }

            // 4. Actualizamos el stock en la base de datos
            $stmt_update = $conexion->prepare("UPDATE productos SET stock = :stock WHERE id = :id");
            $stmt_update->execute([
                ':stock' => $nuevo_stock,
                ':id' => $id_producto
            ]);

            // 4.5. Registramos el movimiento en el historial (con fecha y hora actual)
            $stmt_mov = $conexion->prepare("INSERT INTO movimientos (producto_id, tipo, cantidad, fecha) VALUES (:producto_id, :tipo, :cantidad, NOW())");
            $stmt_mov->execute([
                ':producto_id' => $id_producto,
                ':tipo' => $tipo_movimiento,
                ':cantidad' => $cantidad
            ]);

            // 5. CONFIRMAMOS LA TRANSACCIÓN
            $conexion->commit();
            $mensaje = "¡Movimiento registrado con éxito! El nuevo stock es de $nuevo_stock unidades.";
            $tipo_mensaje = "exito";

            // Actualizar la variable para que el menú desplegable muestre el dato fresco
            foreach ($productos as $key => $prod) {
                if ($prod['id'] == $id_producto) {
                    $productos[$key]['stock'] = $nuevo_stock;
                }
            }

        } catch (Exception $e) {
            $conexion->rollBack();
            $mensaje = $e->getMessage();
            $tipo_mensaje = "error";
        }
    }
}

// 6. OBTENER EL HISTORIAL DE MOVIMIENTOS (se consulta al final para incluir el recién guardado)
$historial = [];
try {
    $stmt_hist = $conexion->query("
        SELECT m.id, p.codigo, p.nombre, m.tipo, m.cantidad, m.fecha 
        FROM movimientos m
        INNER JOIN productos p ON m.producto_id = p.id
        ORDER BY m.fecha DESC, m.id DESC
        LIMIT 10
    ");
    $historial = $stmt_hist->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensaje = "Error al cargar el historial: " . $e->getMessage();
    $tipo_mensaje = "error";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Movimiento - TecnoStock</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #FF8008 0%, #FFA03A 100%);
            padding: 20px; 
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 25px;
        }
        
        .box-movimiento, .box-historial { 
            background-color: rgba(255, 255, 255, 0.94); 
            backdrop-filter: blur(5px);
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 8px 25px rgba(0,0,0,0.2); 
            width: 100%; 
            box-sizing: border-box;
        }
        .box-movimiento { max-width: 450px; }
        .box-historial { max-width: 800px; overflow-x: auto; }
        
        h2 { text-align: center; color: #2c3e50; margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; font-size: 14px; }
        select, input[type="number"] { 
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; outline: none; 
        }
        select:focus, input[type="number"]:focus { border-color: #00c6ff; box-shadow: 0 0 5px rgba(0, 198, 255, 0.3); }

        .btn-guardar { 
            width: 100%; padding: 12px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; 
            border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 10px; transition: all 0.3s;
        }
        .btn-guardar:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        
        .btn-volver {
            display: block; text-align: center; margin-top: 15px; padding: 10px; background-color: #6c757d; color: white;
            text-decoration: none; border-radius: 8px; font-weight: bold; transition: all 0.3s;
        }
        .btn-volver:hover { background-color: #5a6268; }

        .mensaje { padding: 12px; border-radius: 8px; margin-bottom: 15px; text-align: center; font-size: 14px; font-weight: bold; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .exito { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #2c3e50; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .badge-entrada { color: #155724; background-color: #d4edda; padding: 4px 8px; border-radius: 6px; font-weight: bold; }
        .badge-salida { color: #721c24; background-color: #f8d7da; padding: 4px 8px; border-radius: 6px; font-weight: bold; }
    </style>
</head>
<body>

<div class="box-movimiento">
    <h2>🔄 Registrar Movimiento</h2>
    
    <?php if (!empty($mensaje)): ?>
        <div class="mensaje <?php echo $tipo_mensaje; ?>"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="id_producto">Seleccionar Producto</label>
            <select id="id_producto" name="id_producto" required>
                <option value="">-- Elige un producto --</option>
                <?php foreach ($productos as $prod): ?>
                    <option value="<?php echo $prod['id']; ?>">
                        <?php echo htmlspecialchars($prod['codigo'] . " - " . $prod['nombre'] . " (Stock: " . $prod['stock'] . ")"); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="tipo_movimiento">Tipo de Movimiento</label>
            <select id="tipo_movimiento" name="tipo_movimiento" required>
                <option value="">-- Selecciona --</option>
                <option value="entrada">Entrada 🟢 (Aumentar stock)</option>
                <option value="salida">Salida 🔴 (Disminuir stock)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="cantidad">Cantidad</label>
            <input type="number" id="cantidad" name="cantidad" min="1" required placeholder="Ej: 5">
        </div>

        <button type="submit" class="btn-guardar">Guardar Movimiento</button>
    </form>

    <a href="dashboard.php" class="btn-volver">⬅ Volver al Dashboard</a>
</div>

<div class="box-historial">
    <h2> Últimos Movimientos</h2>
    <?php if (empty($historial)): ?>
        <p style="text-align: center; color: #666;">Aún no hay movimientos registrados.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Fecha y Hora</th>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historial as $mov): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($mov['fecha'])); ?></td>
                        <td><?php echo htmlspecialchars($mov['codigo'] . ' - ' . $mov['nombre']); ?></td>
                        <td>
                            <?php if ($mov['tipo'] === 'entrada'): ?>
                                <span class="badge-entrada">Entrada</span>
                            <?php else: ?>
                                <span class="badge-salida">Salida</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo (int)$mov['cantidad']; ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>