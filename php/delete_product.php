<?php
session_start();
include 'conexion.php'; // Incluye el archivo de conexión a la base de datos

// Verifica que el ID del producto esté presente en la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_producto = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    // Asegúrate de que el ID es válido
    if ($id_producto === false) {
        $_SESSION['message'] = "Error: ID de producto inválido.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../dashboard.php");
        exit();
    }

    // Prepara la consulta DELETE
    $stmt = mysqli_prepare($conexion, "DELETE FROM productos WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_producto);

    // Ejecuta la consulta
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Producto eliminado correctamente.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error al eliminar el producto: " . mysqli_error($conexion);
        $_SESSION['message_type'] = "danger";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conexion);

    // Redirecciona al dashboard
    header("Location: ../dashboard.php");
    exit();

} else {
    $_SESSION['message'] = "Acceso no autorizado o ID de producto no especificado.";
    $_SESSION['message_type'] = "danger";
    header("Location: ../dashboard.php");
    exit();
}
?>