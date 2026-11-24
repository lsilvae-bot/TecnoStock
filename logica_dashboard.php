<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ./");
    exit;
}
require 'conexion.php';

$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

try {
    $sql = "SELECT p.*, c.nombre AS nombre_categoria 
            FROM productos p 
            LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
            WHERE p.estado = 1";
    
    $parametros = [];

    if (!empty($busqueda)) {
        $sql .= " AND (p.nombre LIKE :busqueda OR p.codigo LIKE :busqueda OR c.nombre LIKE :busqueda)";
        $parametros[':busqueda'] = '%' . $busqueda . '%';
    }

    $stmt = $conexion->prepare($sql);
    $stmt->execute($parametros);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al obtener los productos: " . $e->getMessage());
}
?>