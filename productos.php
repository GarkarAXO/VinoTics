<?php
error_reporting(E_ALL); // Habilitar todos los reportes de errores
ini_set('display_errors', 1); // Mostrar errores en pantalla (¡SOLO PARA DEBUGGING!)
// session_start(); // La sesión se inicia en layout/header.php
include __DIR__ . '/layout/header.php'; // Incluir el header que ya inicia sesión y conecta DB
include __DIR__ . '/php/conexion.php';

// Mostrar mensajes de sesión (ej. de errores de filtro)
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

// Obtener la categoría del parámetro GET si existe
$selected_category = filter_input(INPUT_GET, 'categoria', FILTER_UNSAFE_RAW);
$min_price = filter_input(INPUT_GET, 'min_price', FILTER_VALIDATE_FLOAT);
$max_price = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT);
$search_term = filter_input(INPUT_GET, 'search_term', FILTER_UNSAFE_RAW);

// Validación de filtros de precio
if ($min_price !== false && $max_price !== false && $min_price > $max_price) {
    $_SESSION['message'] = "Error: El precio mínimo no puede ser mayor que el precio máximo.";
    $_SESSION['message_type'] = "danger";
    // Limpiar los filtros inválidos para que no se apliquen
    $min_price = null;
    $max_price = null;
    // Esto no redirige, solo muestra el mensaje y limpia los valores.
}

// Paginación
$items_per_page = 9; // Número de productos por página
$current_page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
if ($current_page === false || $current_page < 1) {
    $current_page = 1;
}
$offset = ($current_page - 1) * $items_per_page;

// Construir la consulta SQL base para contar el total de productos
$count_query = "SELECT COUNT(*) AS total_products FROM productos";
$where_clauses = [];
$params = [];
$types = "";

if (!empty($selected_category)) {
    $where_clauses[] = "categoria = ?";
    $params[] = $selected_category;
    $types .= "s";
}
// Remove other filter conditions as per user request
/*
if ($min_price !== false && $min_price >= 0) {
    $where_clauses[] = "precio >= ?";
    $params[] = $min_price;
    $types .= "d";
}
if ($max_price !== false && $max_price >= 0) {
    $where_clauses[] = "precio <= ?";
    $params[] = $max_price;
    $types .= "d";
}
if (!empty($search_term)) {
    $where_clauses[] = "nombre LIKE ?";
    $params[] = "%" . $search_term . "%";
    $types .= "s";
}
*/

if (!empty($where_clauses)) {
    $count_query .= " WHERE " . implode(" AND ", $where_clauses);
}

// Ejecutar la consulta para contar el total de productos
$count_stmt = mysqli_prepare($conexion, $count_query);
if (!empty($params)) {
    // Necesitamos reindexar $params para ...$params para que mysqli_stmt_bind_param funcione con una lista dinámica de parámetros
    $ref_params = [];
    foreach ($params as $key => $value) {
        $ref_params[$key] = &$params[$key];
    }
    mysqli_stmt_bind_param($count_stmt, $types, ...$ref_params);
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_products = mysqli_fetch_assoc($count_result)['total_products'];
mysqli_stmt_close($count_stmt);

$total_pages = ceil($total_products / $items_per_page);

// Inicializar $productos como array vacío.
$productos = [];

if (!empty($selected_category)) {
    // CONSTRUCCIÓN Y EJECUCIÓN DE LA CONSULTA PRINCIPAL
    if (!empty($where_clauses)) {
        // --- CASO CON FILTROS: Usar sentencias preparadas ---
        $query_productos = "SELECT * FROM productos WHERE " . implode(" AND ", $where_clauses) . " ORDER BY nombre ASC LIMIT ? OFFSET ?";
        
        $main_params = $params;
        $main_types = $types;
        $main_params[] = $items_per_page;
        $main_params[] = $offset;
        $main_types .= 'ii';

        $stmt = mysqli_prepare($conexion, $query_productos);
        if ($stmt) {
            $ref_params = [];
            foreach ($main_params as $key => $value) {
                $ref_params[$key] = &$main_params[$key];
            }
            mysqli_stmt_bind_param($stmt, $main_types, ...$ref_params);
            mysqli_stmt_execute($stmt);
            $result_productos = mysqli_stmt_get_result($stmt);
            if ($result_productos) {
                while ($row = mysqli_fetch_assoc($result_productos)) {
                    $productos[] = $row;
                }
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        // --- CASO SIN FILTROS: Usar una consulta directa y simple ---
        // Esto se ejecutará si selected_category NO está vacío, pero los otros filtros sí.
        $query_productos = "SELECT * FROM productos ORDER BY nombre ASC LIMIT {$items_per_page} OFFSET {$offset}";
        $result_productos = mysqli_query($conexion, $query_productos);
        if ($result_productos) {
            while ($row = mysqli_fetch_assoc($result_productos)) {
                $productos[] = $row;
            }
        }
    }
}
// Si $selected_category está vacío, $productos se mantiene como un array vacío y se mostrará el mensaje "No hay productos disponibles".


// Obtener todas las categorías para el sidebar
$categorias_sidebar_query = mysqli_query($conexion, "SELECT nombre FROM categorias ORDER BY nombre ASC");
$categorias_sidebar = [];
if ($categorias_sidebar_query) {
    while ($cat = mysqli_fetch_assoc($categorias_sidebar_query)) {
        $categorias_sidebar[] = $cat['nombre'];
    }
}
mysqli_close($conexion);
?>

<title>Productos <?php echo !empty($selected_category) ? ' - ' . htmlspecialchars($selected_category) : ''; ?> - VinoTics</title>

<!-- Contenido de la página de productos -->
<div class="container mt-4">
    <div class="row">
        <!-- Sidebar de Categorías y Filtros -->
        <div class="col-md-3">
            <h4 class="mb-3">Filtrar por Categoría</h4>
            <div class="list-group mb-4">
                <?php foreach ($categorias_sidebar as $cat_name): ?>
                    <a href="productos.php?categoria=<?php echo urlencode($cat_name); ?>" class="list-group-item list-group-item-action <?php echo ($selected_category == $cat_name) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat_name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Listado de Productos -->
        <div class="col-md-9">
            <h1 class="mb-4">
                <?php
                if (!empty($selected_category)) {
                    echo htmlspecialchars($selected_category);
                } else {
                    echo 'Seleccione una categoría';
                }
                ?>
            </h1>
            <div class="row">
                <?php if (!empty($productos)): ?>
                    <?php foreach ($productos as $producto): ?>
                        <?php
                        $precio_original = $producto['precio'];
                        $descuento_porcentaje = $producto['descuento'];
                        $precio_final = $precio_original * (1 - $descuento_porcentaje / 100);

                        // Lógica para determinar el nombre de archivo de la bandera
                        $flag_map = [
                            'MÉXICO' => 'mexico.png',
                            'ESPAÑA' => 'ESPANA.png',
                            'IRLANDA' => 'Irlanda.png',
                            'ESCOCIA' => 'Escocia.png',
                            'REPÚBLICA DOMINICANA' => 'REPUBLICA DOMINICANA.png',
                        ];
                        $pais_producto_normalized = trim(mb_strtoupper(str_replace(['Á','É','Í','Ó','Ú'], ['A','E','I','O', 'U'], $producto['pais'] ?? ''), 'UTF-8'));
                        $flag_filename = $flag_map[$pais_producto_normalized] ?? 'mexico.png'; // Por defecto, bandera de México si no hay coincidencia
                        $flag_path = "/assets/images/paises/" . $flag_filename;
                        ?>
                        <div class="product-card">
                            <div class="productoini">
                                <img class="bandera" src="<?php echo $flag_path; ?>" alt="Bandera de <?php echo htmlspecialchars($producto['pais'] ?? 'Desconocido'); ?>">
                                <a href="/vinospage/detallesproducto.php?id=<?php echo htmlspecialchars($producto['id']); ?>"><img class="vino-bottle" src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>"></a>
                                <div class="informacion">
                                    <span class="precio">$<?php echo htmlspecialchars(number_format($precio_final, 2)); ?></span>
                                    <?php if ($descuento_porcentaje > 0): ?>
                                        <span class="none"><del>$<?php echo htmlspecialchars(number_format($precio_original, 2)); ?></del></span>
                                    <?php endif; ?>
                                    <span class="costo-envio">Envio Gratis</span>
                                    <h2 class="product-title"><?php echo htmlspecialchars($producto['nombre']); ?></h2>
                                    <div>
                                        <button class="btn-agregar" type="button" data-product-id="<?php echo htmlspecialchars($producto['id']); ?>">Agregar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">No hay productos disponibles con los filtros seleccionados.</p>
                <?php endif; ?>
            </div>

            <!-- Controles de Paginación -->
            <nav aria-label="Navegación de productos">
                <ul class="pagination justify-content-center mt-4">
                    <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                        <?php $prev_page_params = array_merge($_GET, ['page' => ($current_page - 1)]); unset($prev_page_params['PHPSESSID']); ?>
                        <a class="page-link" href="?<?php echo htmlspecialchars(http_build_query($prev_page_params)); ?>">Anterior</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                            <?php $page_params = array_merge($_GET, ['page' => $i]); unset($page_params['PHPSESSID']); ?>
                            <a class="page-link" href="?<?php echo htmlspecialchars(http_build_query($page_params)); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                        <?php $next_page_params = array_merge($_GET, ['page' => ($current_page + 1)]); unset($next_page_params['PHPSESSID']); ?>
                        <a class="page-link" href="?<?php echo htmlspecialchars(http_build_query($next_page_params)); ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
