<?php
require_once 'conexion.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // En lugar de borrar, actualizamos el estado a 0 (desactivado)
        $sql = "UPDATE productos SET estado = 0 WHERE id = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Redirigimos de vuelta al inventario
        header("Location: dashboard.php");
        exit;

    } catch (PDOException $e) {
        echo "Error al desactivar el producto: " . $e->getMessage();
    }
} else {
    echo "No se recibió ningún ID.";
}
?>