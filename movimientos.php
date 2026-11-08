<?php
require_once 'conexion.php';

// Obtener los productos para el menú desplegable
try {
    $stmt = $conexion->query("SELECT id, nombre, stock FROM productos ORDER BY nombre ASC");
    $productos = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error al cargar productos: " . $e->getMessage();
    $productos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Movimiento - Tecnostock</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Registrar Movimiento de Inventario</h4>
                </div>
                <div class="card-body">
                    <form action="procesar_movimiento.php" method="POST">
                        
                        <!-- Selección de Producto -->
                        <div class="mb-3">
                            <label for="id_producto" class="form-label">Producto:</label>
                            <select name="id_producto" id="id_producto" class="form-select" required>
                                <option value="">-- Selecciona un producto --</option>
                                <?php foreach ($productos as $prod): ?>
                                    <option value="<?= $prod['id'] ?>">
                                        <?= htmlspecialchars($prod['nombre']) ?> (Stock actual: <?= $prod['stock'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Tipo de Movimiento -->
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo de Movimiento:</label>
                            <select name="tipo" id="tipo" class="form-select" required>
                                <option value="entrada">Entrada (+)</option>
                                <option value="salida">Salida (-)</option>
                            </select>
                        </div>

                        <!-- Cantidad -->
                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad:</label>
                            <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" required>
                        </div>

                        <!-- Observación -->
                        <div class="mb-3">
                            <label for="observacion" class="form-label">Observación (Opcional):</label>
                            <textarea name="observacion" id="observacion" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">Guardar Movimiento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
