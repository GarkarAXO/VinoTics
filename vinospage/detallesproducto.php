<?php
require('../layout/header.php');
include '../php/conexion.php'; // Incluir la conexión a la base de datos

$product = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = $_GET['id'];

    $stmt = mysqli_prepare($conexion, "SELECT * FROM productos WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);
}
mysqli_close($conexion);

if (!$product) {
    // Redireccionar o mostrar un error si el producto no existe o el ID es inválido
    header("Location: ../index.php"); // Redirigir a la página principal
    exit();
}
?>
<title><?php echo htmlspecialchars($product['nombre']); ?></title>

        <div class="contenedor" id="detalles">

            <div class="img__contenedor">

                <div class="images">

                    <!-- Se asume que la imagen principal es la que se guarda en la DB -->

                    <img src="<?php echo htmlspecialchars($product['imagen']); ?>" alt="<?php echo htmlspecialchars($product['nombre']); ?>" class="small-img">

                    <!-- Si hay más imágenes secundarias, se necesitaría una columna adicional o tabla -->

                    <!-- Por ahora, se usará la misma imagen para todas las miniaturas -->

                    <img src="<?php echo htmlspecialchars($product['imagen']); ?>" alt="<?php echo htmlspecialchars($product['nombre']); ?>" class="small-img">

                </div>

                <div class="one-img">

                    <img src="<?php echo htmlspecialchars($product['imagen']); ?>" alt="<?php echo htmlspecialchars($product['nombre']); ?>" class="big-img">

                </div>

            </div>

            <div class="text-contenido">

                <span class="price">$<?php echo htmlspecialchars(number_format($product['precio'], 2)); ?><span class="centavos"></span></span>

                <h2><?php echo htmlspecialchars($product['nombre']); ?></h2>

                <p><?php echo htmlspecialchars($product['descripcion']); ?></p>

                <button class="btn_agregar" data-product-id="<?php echo htmlspecialchars($product['id']); ?>">Añadir al Carrito</button>

            </div>

        </div>

    

        <div class="container mb-4 align-content-center align-items-center text-wrap" style="background: rgb(230, 230, 230);">

            <div class="row">

              <div class="col">

               <h4>DETALLES DEL PRODUCTO:</h4>

               <hr> 

              </div>

            </div>

            <div class="row">

              <div class="col-sm-6">

                <p><?php echo htmlspecialchars($product['maridaje']); ?></p>

              </div>

              <div class="col-sm-3">

                <h6>Marca:</h6>

                <p><?php echo htmlspecialchars($product['marca']); ?></p>

                <h6>Vol. Alcohol:</h6>

                <p><?php echo htmlspecialchars($product['vol_alcohol']); ?></p>

              </div>

              <div class="col-sm-3">

                <h6>Cosecha:</h6>

                <p><?php echo htmlspecialchars($product['cosecha']); ?></p>

                <h6>Pais:</h6>

                <p><?php echo htmlspecialchars($product['pais']); ?></p>

              </div>

            </div>

          </div>

          <?php require('../layout/footer.php') ?>