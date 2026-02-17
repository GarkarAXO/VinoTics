<?php
    // session_start(); // La sesión se inicia en layout/header.php
    // Puedes añadir aquí lógica de protección para asegurar que solo usuarios autorizados accedan
    // Por ejemplo: if(!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') { header("location: login_registro.php"); exit; }

    include 'layout/header.php';
    include 'php/conexion.php';

    $category_id = null;
    $category_name = '';
    $form_title = 'Añadir Nueva Categoría';
    $button_text = 'Añadir Categoría';

    // Lógica para pre-rellenar el formulario si se está editando una categoría
    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
        $category_id = $_GET['edit'];
        $form_title = 'Editar Categoría';
        $button_text = 'Actualizar Categoría';

        $stmt = mysqli_prepare($conexion, "SELECT * FROM categorias WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $category_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $category_data = mysqli_fetch_assoc($result);
            $category_name = htmlspecialchars($category_data['nombre']);
        } else {
            $_SESSION['message'] = "Error: Categoría no encontrada para editar.";
            $_SESSION['message_type'] = "danger";
            header("Location: dashboard_categories.php");
            exit();
        }
        mysqli_stmt_close($stmt);
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Categorías - VinoTics</title>
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Ajusta la ruta si es necesario -->
    <style>
        /* Estilos básicos para el dashboard (se pueden reutilizar los de productos o adaptar) */
        .dashboard-container {
            width: 90%;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .dashboard-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .category-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .category-table th, .category-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .category-table th {
            background-color: #f2f2f2;
            color: #555;
        }
        .category-table tr:nth-child(even) {
            background-color: #fbfbfb;
        }
        .category-table tr:hover {
            background-color: #f1f1f1;
        }
        .category-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-top: 30px;
        }
        .category-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .category-form input[type="text"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .category-form button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .category-form button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Dashboard de Administración de Categorías</h1>

        <!-- Mostrar mensajes de sesión -->
        <?php
        if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            $type = $_SESSION['message_type'] ?? 'info';
            echo "<script>
                        Swal.fire({
                            icon: '{$type}',
                            title: '{$message}',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    </script>";
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>

        <!-- Sección para añadir/editar categorías -->
        <section class="category-form">
            <h2><?php echo $form_title; ?></h2>
            <form action="php/process_category.php" method="POST">
                <?php if ($category_id): ?>
                    <input type="hidden" name="id_categoria" value="<?php echo $category_id; ?>">
                <?php endif; ?>
                <label for="nombre">Nombre de la Categoría:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo $category_name; ?>" required>
                <button type="submit"><?php echo $button_text; ?></button>
            </form>
        </section>

        <!-- Sección para listar categorías -->
        <section>
            <h2>Categorías Existentes</h2>
            <table class="category-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Reabrir conexión si se cerró, o asegurar que esté activa
                        if (!isset($conexion) || !mysqli_ping($conexion)) {
                            include 'php/conexion.php';
                        }
                        $query = "SELECT * FROM categorias ORDER BY nombre ASC";
                        $result = mysqli_query($conexion, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['nombre']) . '</td>';
                                echo '<td>';
                                echo '<a href="?edit=' . htmlspecialchars($row['id']) . '">Editar</a> | ';
                                echo '<a href="php/process_category.php?action=delete&id=' . htmlspecialchars($row['id']) . '" onclick="return confirm(\'¿Estás seguro de eliminar esta categoría? Se desasignará de los productos correspondientes.\')">Eliminar</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="3">No hay categorías para mostrar.</td></tr>';
                        }
                    ?>
                </tbody>
            </table>
        </section>
    </div>

<?php include 'layout/footer.php'; ?>
