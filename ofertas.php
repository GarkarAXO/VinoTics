<?php
require('./layout/header.php');
// La conexión a la base de datos ya está incluida en header.php, así que $conexion debería estar disponible

$ofertas_query = mysqli_query($conexion, "SELECT * FROM productos WHERE descuento > 0 ORDER BY nombre ASC");
$ofertas_productos = [];
if ($ofertas_query) {
    while ($prod = mysqli_fetch_assoc($ofertas_query)) {
        $ofertas_productos[] = $prod;
    }
}
?>
<title>Ofertas - VinoTics</title>

<!--productos-->
<div class="pt-4">
    <h4 class="text-center">Ofertas</h4>
    <hr width="80%">
</div>
<div class="productos">
    <?php if (!empty($ofertas_productos)): ?>
        <?php foreach ($ofertas_productos as $producto): ?>
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
            $pais_producto_normalized = trim(mb_strtoupper(str_replace(['Á','É','Í','Ó','Ú'], ['A','E','I','O','U'], $producto['pais'] ?? ''), 'UTF-8'));
            $flag_filename = $flag_map[$pais_producto_normalized] ?? 'mexico.png'; // Por defecto, bandera de México si no hay coincidencia
            $flag_path = "/assets/images/paises/" . $flag_filename;
            ?>
            <div class="tarjeta">
                <div class="productoini">
                    <img class="bandera" src="<?php echo $flag_path; ?>" alt="Bandera de <?php echo htmlspecialchars($producto['pais'] ?? 'Desconocido'); ?>">
                    <a href="/vinospage/detallesproducto.php?id=<?php echo htmlspecialchars($producto['id']); ?>"><img class="vino-bottle" src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>"></a>
                    <div class="informacion">
                        <span class="precio">$<?php echo htmlspecialchars(number_format($precio_final, 2)); ?> </span>
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
        <p class="text-center">No hay productos en oferta actualmente.</p>
    <?php endif; ?>
</div>

<?php require('./layout/footer.php') ?>
