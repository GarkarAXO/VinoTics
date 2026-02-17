<!---Enlace para agregar el header y links css con php--->
<?php require('./layout/header.php');
      // Mostrar mensajes de sesión (ej. de login exitoso)
      if (isset($_SESSION['login_message']) && !empty($_SESSION['login_message'])) {
          $message = $_SESSION['login_message'];
          $type = $_SESSION['login_message_type'] ?? 'info'; // Default to info if type not set
          echo "<script>
                    Swal.fire({
                        icon: '{$type}',
                        title: '{$message}',
                        showConfirmButton: false,
                        timer: 2000
                    });
                </script>";
          unset($_SESSION['login_message']);
          unset($_SESSION['login_message_type']);
      }
      
      include 'php/conexion.php'; // Incluir la conexión a la base de datos

      $query_productos = "SELECT * FROM productos";
      $result_productos = mysqli_query($conexion, $query_productos);
      $productos = [];
      if ($result_productos && mysqli_num_rows($result_productos) > 0) {
          while ($row = mysqli_fetch_assoc($result_productos)) {
              $productos[] = $row;
          }
      }
      mysqli_close($conexion);
?>
<!--titulo de la pagina-->
<title>Inicio</title>
<!--Slider de productos nuevos-->
<div class="container m-0 p-0 pb-5 mw-100 bg-light">
<div class="row">
<div class="col-sm-6">
<div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img class="d-block w-100" src="assets/images/carousel/absolute.jpg" alt="First slide" width="100px">
      </div>
      <div class="carousel-item">
        <img class="d-block w-100" src="assets/images/carousel/Buchanns.jpg" alt="Second slide" width="100px">
      </div>
      <div class="carousel-item">
        <img class="d-block w-100" src="assets/images/carousel/jhonny walker.jpg" alt="Third slide" width="100px">
      </div>
    </div>
    <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="sr-only">Next</span>
    </a>
  </div>
</div>
    <div class="col-sm-6">
      <div class="m-1">
     <h4 class="text-center">Vinos</h4>
      <p class="text-center">El vino es una bebida hecha de uva, mediante la fermentación alcohólica de su mosto o zumo.​ La fermentación se produce por la acción metabólica de levaduras, que transforman los azúcares naturales del fruto en etanol y gas en forma de dióxido de carbono.</p>
      <a href="/blog/blog.html"><img src="/assets/images/banner/blog.png" alt="" width="100%"></a>
    </div>
   </div>
</div>
</div>
</div>

<!--carrusel de productos-->
<section class="product"> 
  <h4 class="product-category">PRODUCTOS DESTACADOS</h4>
  <button class="pre-btn"><i class="fa-solid fa-chevron-left"></i></button>
  <button class="nxt-btn"><i class="fa-solid fa-chevron-right"></i></button>
  <div class="product-container">
    <?php if (!empty($productos)): ?>
        <?php foreach ($productos as $producto): ?>
            <div class="product-card">
              <div class="productoini">
                <?php
                // Mapeo de nombres de país a nombres de archivo de bandera
                $flag_map = [
                    'MÉXICO' => 'mexico.png',
                    'ESPAÑA' => 'ESPANA.png',
                    'IRLANDA' => 'Irlanda.png',
                    'ESCOCIA' => 'Escocia.png',
                    'REPÚBLICA DOMINICANA' => 'REPUBLICA DOMINICANA.png',
                ];
                // Normalizar el nombre del país del producto para la búsqueda (mayúsculas, sin acentos para evitar problemas)
                $pais_producto_normalized = trim(mb_strtoupper(str_replace(['Á','É','Í','Ó','Ú'], ['A','E','I','O','U'], $producto['pais'] ?? ''), 'UTF-8'));
                
                $flag_filename = $flag_map[$pais_producto_normalized] ?? 'mexico.png'; // Por defecto, bandera de México si no hay coincidencia

                $flag_path = "/assets/images/paises/" . $flag_filename;
                ?>
                <img class="bandera" src="<?php echo $flag_path; ?>" alt="Bandera de <?php echo htmlspecialchars($producto['pais'] ?? 'Desconocido'); ?>">
                <a href="/vinospage/detallesproducto.php?id=<?php echo htmlspecialchars($producto['id']); ?>"><img class="vino-bottle" src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>"></a>
                <div class="informacion">
                   <span class="precio">$<?php echo htmlspecialchars(number_format($producto['precio'], 2)); ?></span>
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
        <p>No hay productos disponibles.</p>
    <?php endif; ?>
  </div>
</section>


<img src="/assets/images/banner/WineClass-op.png" alt="" width="100%">

<?php require('./layout/footer.php') ?>

