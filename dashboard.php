<?php
    // Puedes añadir aquí lógica de protección para asegurar que solo usuarios autorizados accedan al dashboard
    // Por ejemplo: if(!isset($_SESSION['usuario'])) { header("location: login_registro.php"); exit; }

    include 'layout/header.php'; // Asumiendo que header.php está en la carpeta layout
    include 'php/conexion.php'; // Incluir la conexión a la base de datos

    $product_id = null;
    $product_name = '';
    $product_description = '';
    $product_price = '';
    $product_image = '';
    $product_category = '';
    $product_country = ''; // Nuevo campo para el país
    $product_marca = '';
    $product_vol_alcohol = '';
    $product_cosecha = '';
    $product_maridaje = '';
    $product_descuento = '0.00'; // Nuevo campo para el porcentaje de descuento
    $form_title = 'Añadir Nuevo Producto';
    $button_text = 'Añadir Producto';

    // Obtener categorías para el selector
    // Reabrir conexión si se cerró, o asegurar que esté activa
    if (!isset($conexion) || !mysqli_ping($conexion)) {
        include 'php/conexion.php';
    }
    $categorias_query = mysqli_query($conexion, "SELECT id, nombre FROM categorias ORDER BY nombre ASC");
    $categorias_list = [];
    if ($categorias_query) {
        while ($cat = mysqli_fetch_assoc($categorias_query)) {
            $categorias_list[] = $cat;
        }
    }

    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
        $product_id = $_GET['edit'];
        $form_title = 'Editar Producto';
        $button_text = 'Actualizar Producto';

        $stmt = mysqli_prepare($conexion, "SELECT * FROM productos WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $product_data = mysqli_fetch_assoc($result);
            $product_name = htmlspecialchars($product_data['nombre']);
            $product_description = htmlspecialchars($product_data['descripcion']);
            $product_price = htmlspecialchars($product_data['precio']);
            $product_image = htmlspecialchars($product_data['imagen']);
            $product_category = htmlspecialchars($product_data['categoria']);
            $product_country = htmlspecialchars($product_data['pais']); // Nuevo campo para el país
            $product_marca = htmlspecialchars($product_data['marca']);
            $product_vol_alcohol = htmlspecialchars($product_data['vol_alcohol']);
            $product_cosecha = htmlspecialchars($product_data['cosecha']);
            $product_maridaje = htmlspecialchars($product_data['maridaje']);
            $product_descuento = htmlspecialchars($product_data['descuento']); // Nuevo campo para el porcentaje de descuento
        } else {
            // Producto no encontrado, redireccionar o mostrar error
            $_SESSION['message'] = "Error: Producto no encontrado para editar.";
            $_SESSION['message_type'] = "danger";
            header("Location: dashboard.php");
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
    <title>Dashboard de Productos - VinoTics</title>
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Ajusta la ruta si es necesario -->
    <style>
        /* Estilos básicos para el dashboard */
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
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .product-table th, .product-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .product-table th {
            background-color: #f2f2f2;
            color: #555;
        }
        .product-table tr:nth-child(even) {
            background-color: #fbfbfb;
        }
        .product-table tr:hover {
            background-color: #f1f1f1;
        }
        .product-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-top: 30px;
        }
        .product-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .product-form input[type="text"],
        .product-form input[type="number"],
        .product-form textarea,
        .product-form select {
            width: calc(100% - 22px); /* Ajustar el ancho para padding y borde */
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .product-form textarea {
            resize: vertical;
            min-height: 80px;
        }
        .product-form button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .product-form button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Dashboard de Administración de Productos</h1>

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

        <!-- Sección para listar productos -->
        <section>
            <h2>Productos Existentes</h2>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Imagen</th>
                        <th>Categoría</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $query = "SELECT * FROM productos";
                        $result = mysqli_query($conexion, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['nombre']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['descripcion']) . '</td>';
                                echo '<td>$' . htmlspecialchars(number_format($row['precio'], 2)) . '</td>';
                                echo '<td><img src="' . htmlspecialchars($row['imagen']) . '" alt="Imagen de ' . htmlspecialchars($row['nombre']) . '" width="50"></td>';
                                echo '<td>' . htmlspecialchars($row['categoria']) . '</td>';
                                echo '<td>';
                                echo '<a href="?edit=' . htmlspecialchars($row['id']) . '">Editar</a> | ';
                                echo '<a href="php/delete_product.php?id=' . htmlspecialchars($row['id']) . '" onclick="return confirm(\'¿Estás seguro de eliminar este producto?\')">Eliminar</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="7">No hay productos para mostrar.</td></tr>';
                        }
                    ?>
                </tbody>
            </table>
        </section>

        <!-- Sección para añadir/editar productos -->
        <section class="product-form">
            <h2><?php echo $form_title; ?></h2>
            <form action="php/process_product.php" method="POST" enctype="multipart/form-data">
                <?php if ($product_id): ?>
                    <input type="hidden" name="id_producto" value="<?php echo $product_id; ?>">
                <?php endif; ?>
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo $product_name; ?>" required>

                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion"><?php echo $product_description; ?></textarea>

                <label for="precio">Precio:</label>
                <input type="number" id="precio" name="precio" step="0.01" value="<?php echo $product_price; ?>" required>

                <label for="descuento">Descuento (%):</label>
                <input type="number" id="descuento" name="descuento" step="0.01" min="0" max="100" value="<?php echo $product_descuento; ?>">

                <label for="imagen">Imagen:</label>
                <p>Imagen actual: <?php echo !empty($product_image) ? '<img src="' . htmlspecialchars($product_image) . '" width="50">' : 'Ninguna'; ?></p>
                <input type="file" id="imagen_file" name="imagen_file" accept="image/*">
                <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($product_image); ?>">
                <p>O introduce URL de imagen (si no subes un archivo):</p>
                <input type="text" id="imagen_url" name="imagen_url" placeholder="Introduce URL de la imagen" value="">

                <label for="categoria">Categoría:</label>
                <select id="categoria" name="categoria" required>
                    <option value="">-- Selecciona una Categoría --</option>
                    <?php foreach ($categorias_list as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['nombre']); ?>" <?php echo ($product_category == $cat['nombre']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p><a href="dashboard_categories.php">Administrar Categorías</a></p>

                <label for="pais">País de Origen:</label>
                <input type="text" id="pais" name="pais" value="<?php echo $product_country; ?>" placeholder="Ej: México, España" required>

                <label for="marca">Marca:</label>
                <input type="text" id="marca" name="marca" value="<?php echo $product_marca; ?>" required>

                <label for="vol_alcohol">Vol. Alcohol:</label>
                <input type="text" id="vol_alcohol" name="vol_alcohol" value="<?php echo $product_vol_alcohol; ?>" placeholder="Ej: 13.5%" required>

                <label for="cosecha">Cosecha:</label>
                <input type="text" id="cosecha" name="cosecha" value="<?php echo $product_cosecha; ?>" placeholder="Ej: 2018, NV" required>

                <label for="maridaje">Maridaje (Sugerencia de Consumo):</label>
                <textarea id="maridaje" name="maridaje"><?php echo $product_maridaje; ?></textarea>

                <button type="submit"><?php echo $button_text; ?></button>
            </form>
        </section>
    </div>

<?php include 'layout/footer.php'; // Asumiendo que footer.php está en la carpeta layout ?>
