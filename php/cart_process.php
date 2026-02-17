<?php
session_start();
include 'conexion.php'; // Incluye el archivo de conexión a la base de datos

header('Content-Type: application/json'); // Asegura que la respuesta sea JSON

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function calculate_cart_totals($cart) {
    $total_items = 0;
    $total_price = 0.0;
    foreach ($cart as $item) {
        $total_items += $item['quantity'];
        $total_price += $item['price'] * $item['quantity'];
    }
    return ['total_items' => $total_items, 'total_price' => $total_price];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'add':
            $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

            if (!$product_id || !$quantity || $quantity <= 0) {
                $response['message'] = 'ID de producto o cantidad inválidos.';
                echo json_encode($response);
                exit();
            }

            // Obtener detalles del producto de la base de datos
            $stmt = mysqli_prepare($conexion, "SELECT id, nombre, precio, imagen FROM productos WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $product_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $product_data = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($product_data) {
                // Añadir al carrito o actualizar cantidad
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
                } else {
                    $_SESSION['cart'][$product_id] = [
                        'id' => $product_data['id'],
                        'name' => $product_data['nombre'],
                        'price' => $product_data['precio'],
                        'image' => $product_data['imagen'],
                        'quantity' => $quantity
                    ];
                }
                $totals = calculate_cart_totals($_SESSION['cart']);
                $response['success'] = true;
                $response['message'] = 'Producto añadido al carrito.';
                $response['cart_count'] = $totals['total_items'];
                $response['cart_total'] = number_format($totals['total_price'], 2);
                $response['cart_items'] = array_values($_SESSION['cart']); // Para enviar los items del carrito
            } else {
                $response['message'] = 'Producto no encontrado.';
            }
            break;

        case 'remove':
            $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            if ($product_id && isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
                $totals = calculate_cart_totals($_SESSION['cart']);
                $response['success'] = true;
                $response['message'] = 'Producto eliminado del carrito.';
                $response['cart_count'] = $totals['total_items'];
                $response['cart_total'] = number_format($totals['total_price'], 2);
                $response['cart_items'] = array_values($_SESSION['cart']);
            } else {
                $response['message'] = 'Producto no encontrado en el carrito.';
            }
            break;

        case 'update_quantity':
            $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            $new_quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

            if ($product_id && isset($_SESSION['cart'][$product_id]) && $new_quantity !== false && $new_quantity >= 0) {
                if ($new_quantity == 0) {
                    unset($_SESSION['cart'][$product_id]);
                    $response['message'] = 'Producto eliminado del carrito.';
                } else {
                    $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
                    $response['message'] = 'Cantidad del producto actualizada.';
                }
                $totals = calculate_cart_totals($_SESSION['cart']);
                $response['success'] = true;
                $response['cart_count'] = $totals['total_items'];
                $response['cart_total'] = number_format($totals['total_price'], 2);
                $response['cart_items'] = array_values($_SESSION['cart']);
            } else {
                $response['message'] = 'ID de producto o cantidad inválidos.';
            }
            break;
            
        case 'get_cart':
            $totals = calculate_cart_totals($_SESSION['cart']);
            $response['success'] = true;
            $response['cart_count'] = $totals['total_items'];
            $response['cart_total'] = number_format($totals['total_price'], 2);
            $response['cart_items'] = array_values($_SESSION['cart']);
            break;

        default:
            $response['message'] = 'Acción inválida.';
            break;
    }
} else {
    $response['message'] = 'Solicitud inválida.';
}

mysqli_close($conexion);
echo json_encode($response);
exit();
?>
