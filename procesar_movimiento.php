<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = $_POST['id_producto'] ?? null;
    $tipo        = $_POST['tipo'] ?? null;
    $cantidad    = intval($_POST['cantidad'] ?? 0);
    $observacion = $_POST['observacion'] ?? '';
    // ID de usuario fijo para pruebas (ajustar cuando tengas el sistema de inicio de sesión/session)
    $id_usuario  = 1; 

    if (!$id_producto || !$tipo || $cantidad <= 0) {
        die("Error: Datos incompletos o inválidos.");
    }

    try {
        // Iniciar transacción para asegurar la integridad de la base de datos
        $conexion->beginTransaction();

        // 1. Obtener el stock actual del producto
        $stmt = $conexion->prepare("SELECT stock FROM productos WHERE id = ?");
        $stmt->execute([$id_producto]);
        $producto = $stmt->fetch();

        if (!$producto) {
            throw new Exception("El producto seleccionado no existe.");
        }

        $stock_actual = $producto['stock'];

        // Validar stock suficiente si el movimiento es una salida
        if ($tipo === 'salida' && $cantidad > $stock_actual) {
            throw new Exception("Stock insuficiente. Stock actual: " . $stock_actual);
        }

        // 2. Registrar el movimiento en la tabla 'movimientos'
        $sqlMov = "INSERT INTO movimientos (id_producto, id_usuario, tipo, cantidad, observacion, fecha_hora) 
                   VALUES (?, ?, ?, ?, ?, NOW())";
        $stmtMov = $conexion->prepare($sqlMov);
        $stmtMov->execute([$id_producto, $id_usuario, $tipo, $cantidad, $observacion]);

        // 3. Actualizar el stock en la tabla 'productos'
        if ($tipo === 'entrada') {
            $nuevo_stock = $stock_actual + $cantidad;
        } else {
            $nuevo_stock = $stock_actual - $cantidad;
        }

        $sqlStock = "UPDATE productos SET stock = ? WHERE id = ?";
        $stmtStock = $conexion->prepare($sqlStock);
        $stmtStock->execute([$nuevo_stock, $id_producto]);

        // Confirmar transacción
        $conexion->commit();

        echo "<script>
                alert('¡Movimiento registrado y stock actualizado con éxito!');
                window.location.href = 'movimientos.php';
              </script>";

    } catch (Exception $e) {
        // Revertir cambios si algo falla
        $conexion->rollBack();
        echo "Error al procesar el movimiento: " . $e->getMessage();
    }
} else {
    header("Location: movimientos.php");
    exit;
}
?>