<?php
session_start();
// error_reporting(E_ALL); // Habilitar todos los reportes de errores (Desactivado para producción)
// ini_set('display_errors', 1); // Mostrar errores en pantalla (Desactivado para producción)
include 'conexion.php'; // Incluye el archivo de conexión a la base de datos

// Verifica que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Recoge y sanea los datos del formulario
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
    $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
    $categoria = filter_input(INPUT_POST, 'categoria', FILTER_SANITIZE_STRING);
    $existing_image = filter_input(INPUT_POST, 'existing_image', FILTER_SANITIZE_URL); // Imagen existente para actualizaciones
    $imagen = $existing_image; // Por defecto, se mantiene la imagen existente

    // Manejo de la subida de archivos

    $target_image_path = $existing_image; // Por defecto, se mantiene la imagen existente

    // Procesa la subida del archivo si se intenta
    if (isset($_FILES['imagen_file']) && $_FILES['imagen_file']['name'] != '') {
        if ($_FILES['imagen_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_name = $_FILES['imagen_file']['tmp_name'];
            $file_name = $_FILES['imagen_file']['name'];
            $file_size = $_FILES['imagen_file']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_ext = ['jpeg', 'jpg', 'png', 'gif'];
            $web_root_upload_path = '/assets/images/productos_vinos/'; // Ruta relativa al root web para guardar en DB
            $full_upload_dir = __DIR__ . '/..' . $web_root_upload_path; // Ruta absoluta para move_uploaded_file

            // Asegurarse de que el directorio de subida existe y tiene permisos de escritura
            if (!is_dir($full_upload_dir)) {
                // error_log("Upload directory does not exist: " . $full_upload_dir); // Debug
                if (!mkdir($full_upload_dir, 0775, true)) { // Crear recursivamente con permisos 0775
                    $_SESSION['message'] = "Error: No se pudo crear el directorio de subida de imágenes. Verifique permisos.";
                    $_SESSION['message_type'] = "danger";
                    header("Location: ../dashboard.php");
                    exit();
                }
                // error_log("Upload directory created: " . $full_upload_dir); // Debug
            } else {
                // error_log("Upload directory exists: " . $full_upload_dir); // Debug
            }

            if (in_array($file_ext, $allowed_ext)) {
                if ($file_size < 5000000) { // Max 5MB
                    $new_file_name = uniqid('prod_') . '.' . $file_ext;
                    $destination = $full_upload_dir . $new_file_name; // Ruta absoluta para mover el archivo

                    if (move_uploaded_file($file_tmp_name, $destination)) {
                        $target_image_path = $web_root_upload_path . $new_file_name; // Guardar la ruta relativa al web root para la DB
                        // error_log("File moved successfully. Image path: " . $target_image_path); // Debug
                    } else {
                        // error_log("Failed to move uploaded file from " . $file_tmp_name . " to " . $destination); // Debug
                        $_SESSION['message'] = "Error al mover el archivo subido al servidor.";
                        $_SESSION['message_type'] = "danger";
                        header("Location: ../dashboard.php");
                        exit();
                    }
                } else {
                    $_SESSION['message'] = "El tamaño del archivo es demasiado grande (máx. 5MB).";
                    $_SESSION['message_type'] = "danger";
                    header("Location: ../dashboard.php");
                    exit();
                }
            } else {
                $_SESSION['message'] = "Tipo de archivo no permitido. Solo JPG, JPEG, PNG, GIF.";
                $_SESSION['message_type'] = "danger";
                header("Location: ../dashboard.php");
                exit();
            }
        } else {
            // Handle specific upload errors if a file was attempted to be uploaded but failed
            $error_code = $_FILES['imagen_file']['error'];
            $php_upload_errors = [
                UPLOAD_ERR_INI_SIZE => "El archivo subido excede la directiva upload_max_filesize en php.ini.",
                UPLOAD_ERR_FORM_SIZE => "El archivo subido excede la directiva MAX_FILE_SIZE que fue especificada en el formulario HTML.",
                UPLOAD_ERR_PARTIAL => "El archivo subido sólo fue parcialmente cargado.",
                UPLOAD_ERR_NO_FILE => "No se subió ningún archivo.", // This case is often hit if file input is left empty
                UPLOAD_ERR_NO_TMP_DIR => "Falta la carpeta temporal.",
                UPLOAD_ERR_CANT_WRITE => "Fallo al escribir el archivo al disco.",
                UPLOAD_ERR_EXTENSION => "Una extensión de PHP detuvo la carga del archivo.",
            ];
            $_SESSION['message'] = "Error de subida de archivo: " . ($php_upload_errors[$error_code] ?? "Código de error: {$error_code}");
            $_SESSION['message_type'] = "danger";
            header("Location: ../dashboard.php");
            exit();
        }
    } else if (!empty($_POST['imagen_url'])) { // Si no se subió archivo, verificar si se proporcionó una URL
        $target_image_path = filter_input(INPUT_POST, 'imagen_url', FILTER_SANITIZE_URL);
    }
    $imagen = $target_image_path; // Actualizar la variable $imagen que se usará en la DB
    $pais = filter_input(INPUT_POST, 'pais', FILTER_SANITIZE_STRING); // Nuevo campo para el país
    $marca = filter_input(INPUT_POST, 'marca', FILTER_SANITIZE_STRING);
    $vol_alcohol = filter_input(INPUT_POST, 'vol_alcohol', FILTER_SANITIZE_STRING);
    $cosecha = filter_input(INPUT_POST, 'cosecha', FILTER_SANITIZE_STRING);
    $maridaje = filter_input(INPUT_POST, 'maridaje', FILTER_SANITIZE_STRING);
    $descuento = filter_input(INPUT_POST, 'descuento', FILTER_VALIDATE_FLOAT); // Nuevo campo para el descuento
    if ($descuento === false || $descuento < 0 || $descuento > 100) {
        $descuento = 0.00; // Valor por defecto si es inválido
    }
    // error_log("Final \$imagen value before DB operations: " . $imagen); // Debug
    // error_log("Category value before DB operations: " . $categoria); // Debug
    $id_producto = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT); // Para edición

    // Validación básica de los campos requeridos
    if (empty($nombre) || empty($precio) || empty($categoria) || empty($pais) || empty($marca) || empty($vol_alcohol) || empty($cosecha)) {
        $_SESSION['message'] = "Error: Nombre, precio, categoría, país, marca, vol. alcohol y cosecha son campos obligatorios.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../dashboard.php");
        exit();
    }

    // Si se recibe un id_producto, es una actualización
    if ($id_producto) {
        $stmt = mysqli_prepare($conexion, "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, imagen = ?, categoria = ?, pais = ?, marca = ?, vol_alcohol = ?, cosecha = ?, maridaje = ?, descuento = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssdssssssddi", $nombre, $descripcion, $precio, $imagen, $categoria, $pais, $marca, $vol_alcohol, $cosecha, $maridaje, $descuento, $id_producto);
        $action_type = "actualizado";
    } else { // Si no hay id_producto, es una inserción
        $stmt = mysqli_prepare($conexion, "INSERT INTO productos (nombre, descripcion, precio, imagen, categoria, pais, marca, vol_alcohol, cosecha, maridaje, descuento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssdsssssssd", $nombre, $descripcion, $precio, $imagen, $categoria, $pais, $marca, $vol_alcohol, $cosecha, $maridaje, $descuento);
        $action_type = "añadido";
    }

    // Ejecuta la consulta común
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Producto $action_type correctamente.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error al procesar el producto: " . mysqli_error($conexion);
        $_SESSION['message_type'] = "danger";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conexion);

    // Redirecciona al dashboard
    header("Location: ../dashboard.php");
    exit();


} else {
    // Si no es una solicitud POST, redirecciona con un mensaje de error
    $_SESSION['message'] = "Acceso no autorizado.";
    $_SESSION['message_type'] = "danger";
    header("Location: ../dashboard.php");
    exit();
}
?>
