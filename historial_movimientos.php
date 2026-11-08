<?php
include 'db.php'; // Asegúrate de ajustar el nombre de tu archivo de conexión a la base de datos
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Movimientos - TecnoStock</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Historial de Movimientos de Inventario</h2>
        <div>
            <a href="dashboard.php" class="btn btn-secondary">Volver al Dashboard</a>
            <a href="movimientos.php" class="btn btn-primary">Registrar Movimiento</a>
        </div>
    </div>

    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Fecha y Hora</th>
                <th>Producto</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Usuario</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT m.id_movimiento, m.fecha_hora, p.nombre AS producto, m.tipo, m.cantidad, u.usuario, m.observacion 
                    FROM movimientos m
                    JOIN productos p ON m.id_producto = p.id
                    JOIN usuarios u ON m.id_usuario = u.id
                    ORDER BY m.fecha_hora DESC";
            
            $resultado = $conexion->query($sql);

            if ($resultado && $resultado->num_rows > 0) {
                while ($fila = $resultado->fetch_assoc()) {
                    $claseTipo = ($fila['tipo'] == 'entrada') ? 'badge bg-success' : 'badge bg-danger';
                    echo "<tr>";
                    echo "<td>" . $fila['id_movimiento'] . "</td>";
                    echo "<td>" . $fila['fecha_hora'] . "</td>";
                    echo "<td>" . htmlspecialchars($fila['producto']) . "</td>";
                    echo "<td><span class='" . $claseTipo . "'>" . strtoupper($fila['tipo']) . "</span></td>";
                    echo "<td>" . $fila['cantidad'] . "</td>";
                    echo "<td>" . htmlspecialchars($fila['usuario']) . "</td>";
                    echo "<td>" . htmlspecialchars($fila['observacion']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='text-center'>No hay movimientos registrados.</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>