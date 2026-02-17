<?php
session_start();
include 'conexion.php'; // Incluye el archivo de conexión a la base de datos

header('Location: ../dashboard_categories.php'); // Redirección por defecto
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombre'])) {
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $id_categoria = filter_input(INPUT_POST, 'id_categoria', FILTER_VALIDATE_INT);

    if (empty($nombre)) {
        $_SESSION['message'] = "El nombre de la categoría no puede estar vacío.";
        $_SESSION['message_type'] = "danger";
        exit();
    }

    // Verificar si el nombre de la categoría ya existe
    $check_stmt = mysqli_prepare($conexion, "SELECT COUNT(*) FROM categorias WHERE nombre = ? AND id != ?");
    $check_id = $id_categoria ? $id_categoria : 0; // Si es nuevo, el ID para la exclusión es 0
    mysqli_stmt_bind_param($check_stmt, "si", $nombre, $check_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_bind_result($check_stmt, $count);
    mysqli_stmt_fetch($check_stmt);
    mysqli_stmt_close($check_stmt);

    if ($count > 0) {
        $_SESSION['message'] = "Ya existe una categoría con ese nombre.";
        $_SESSION['message_type'] = "danger";
        exit();
    }

    if ($id_categoria) { // Actualizar categoría existente
        $stmt = mysqli_prepare($conexion, "UPDATE categorias SET nombre = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $nombre, $id_categoria);
        $action_type = "actualizada";
    } else { // Añadir nueva categoría
        $stmt = mysqli_prepare($conexion, "INSERT INTO categorias (nombre) VALUES (?)");
        mysqli_stmt_bind_param($stmt, "s", $nombre);
        $action_type = "añadida";
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Categoría $action_type correctamente.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error al procesar la categoría: " . mysqli_error($conexion);
        $_SESSION['message_type'] = "danger";
    }
    mysqli_stmt_close($stmt);

} elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_categoria = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$id_categoria) {
        $_SESSION['message'] = "ID de categoría inválido.";
        $_SESSION['message_type'] = "danger";
        exit();
    }

    // Opcional: Desvincular productos de esta categoría antes de eliminarla
    // mysqli_query($conexion, "UPDATE productos SET categoria = NULL WHERE categoria = (SELECT nombre FROM categorias WHERE id = $id_categoria)");
    // Nota: Esto requeriría cambiar 'categoria' en la tabla 'productos' a INT (FK a categorias.id) para una mejor gestión.
    // Para simplificar, se eliminará directamente la categoría.

    $stmt = mysqli_prepare($conexion, "DELETE FROM categorias WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_categoria);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Categoría eliminada correctamente.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error al eliminar la categoría: " . mysqli_error($conexion);
        $_SESSION['message_type'] = "danger";
    }
    mysqli_stmt_close($stmt);

} else {
    $_SESSION['message'] = "Acceso no autorizado o acción inválida.";
    $_SESSION['message_type'] = "danger";
}

mysqli_close($conexion);
exit();
?>
