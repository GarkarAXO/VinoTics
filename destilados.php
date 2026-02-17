<?php require('./layout/header.php') ?>
<title>Vinos Mexicanos</title>

<!--productos-->
<div class="pt-4">
    <h4 class="text-center">Destilados</h4>
    <hr width="80%">
</div>

<div class="productos">
    <div class="tarjeta">
      <div class="productoini">
          <img class="bandera" src="assets/images/paises/Irlanda.png" alt="Pais">
          <a href="/vinospage/baileyschurros.php"><img class="vino-bottle" src="assets/images/productos vinos/destilados/baileysChurros.jpg" alt=""></a>
          <div class="informacion">
             <span class="precio">$479.00</span>
             <span class="costo-envio">Envio Gratis</span>
             <h2 class="product-title">Baileys Churros</h2>
            <div>
              <button class="btn-agregar" type="button">Agregar</button>
            </div>
          </div>
      </div>
    </div>

    <div class="tarjeta">
      <div class="productoini">
            <img class="bandera" src="assets/images/paises/Escocia.png" alt="Pais">
            <a href="/vinospage/"><img src="assets/images/productos vinos/destilados/buchanans.jpg" class="vino-bottle" alt=""></a>
            <div class="informacion">
              <span class="precio">$661.50</span>
              <span class="costo-envio">Envio Gratis</span>
              <h2 class="product-title">Buchanans 12 Años</h2>
              <div>
                <button class="btn-agregar" type="button">Agregar</button>
              </div>
            </div>
        </div>
    </div>
</div>

<?php require('./layout/footer.php') ?>
