<?php require 'logica_dashboard.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TecnoStock</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

    <div class="container">
        <h2>Inventario de TecnoStock</h2>
        
        <div class="controles">
            <div class="botones-izquierda">
                <a href="agregar.php" class="btn btn-agregar">➕ Agregar Producto</a>
                <a href="movimientos.php" class="btn btn-movimientos">🔄 Movimientos</a>
                <a href="categorias.php" class="btn btn-categorias">📂 Categorías</a>
                <a href="logout.php" class="btn btn-logout">🚪 Salir</a>
            </div>
            
            <form method="GET" action="dashboard.php" class="buscador">
                <input type="text" name="buscar" placeholder="Buscar por código, nombre o categoría..." value="<?php echo htmlspecialchars($busqueda); ?>">
                <button type="submit" class="btn btn-buscar">🔍 Buscar</button>
                <?php if (!empty($busqueda)): ?>
                    <a href="dashboard.php" class="btn btn-limpiar">✖ Limpiar</a>
                <?php endif; ?>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($productos) > 0): ?>
                    <?php foreach ($productos as $producto): ?>
                        <tr <?php if ($producto['stock'] < $producto['stock_minimo']) echo 'style="background-color: #fff3cd;" title="¡Atención! Stock bajo"'; ?>>
                            <td><strong><?php echo htmlspecialchars($producto['codigo'] ?? 'N/A'); ?></strong></td>
                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($producto['nombre_categoria'] ?? 'Sin categoría'); ?></td>
                            
                            <td class="td-descripcion" title="<?php echo htmlspecialchars($producto['descripcion']); ?>">
                                <?php echo htmlspecialchars($producto['descripcion']); ?>
                            </td>
                            
                            <td class="td-precio">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></td>
                            
                            <td>
                                <span class="badge-stock <?php echo ($producto['stock'] < $producto['stock_minimo']) ? 'badge-alerta' : ''; ?>">
                                    <?php echo $producto['stock']; ?>
                                    <?php if ($producto['stock'] < $producto['stock_minimo']) echo ' ⚠️'; ?>
                                </span>
                            </td>
                            
                            <td class="acciones">
                                <a href="editar.php?id=<?php echo $producto['id']; ?>" class="btn-accion btn-editar">✏️ Editar</a> 
                                <a href="eliminar.php?id=<?php echo $producto['id']; ?>" class="btn-accion btn-eliminar" onclick="return confirm('¿Estás seguro de que deseas desactivar este producto?');">🗑️ Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px; color: #777;">No se encontraron productos en el inventario.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>