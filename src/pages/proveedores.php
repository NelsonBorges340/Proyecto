<?php
 if (session_status() === PHP_SESSION_NONE) {
    session_start();
}?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>hirenear-poveedores</title>
  <link rel="shortcut icon" href="../assets/img/logo.svg">

  <!-- LINKS FONTS -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap"
    rel="stylesheet">
  <!-- LINKS CSS -->

  <link rel="stylesheet" href="../assets/css/footer-nav-body.css">
  <link rel="stylesheet" href="../assets/css/proveedores.css">
  
</head>

<body>
          <!--header-->
          <?php include '../Direcciones.php';
                include UTIL_URL.'header.php'; ?>

        
  <hr class="hr-general">

  <main>
    <div class="contenedor">
      <header>
        <h1><span>Hirenear</span> - Explorar Servicios</h1>
        <p>Filtra por servicio, ubicación, precio y más</p>
      </header>

      <div class="distribucion">
        <!-- Panel de filtros -->
        <aside class="panel filtros">
          <div class="seccion busqueda">
            <input id="q" placeholder="Buscar servicio" />
            <div><button id="btnLimpiar">Limpiar</button></div>
          </div>

          <div class="seccion">
            <h3>Etiquetas</h3>
            <div class="contenedor-etiquetas" id="chips">
              <button class="etiqueta" data-etiqueta="eléctrico">eléctrico</button>
              <button class="etiqueta" data-etiqueta="plomería">plomería</button>
              <button class="etiqueta" data-etiqueta="web">web</button>
              <button class="etiqueta" data-etiqueta="educación">educación</button>
              <button class="etiqueta" data-etiqueta="jardín">jardín</button>
              <button class="etiqueta" data-etiqueta="foto">foto</button>
              <button class="etiqueta" data-etiqueta="madera">madera</button>
              <button class="etiqueta" data-etiqueta="diseño">diseño</button>
              <button class="etiqueta" data-etiqueta="auto">auto</button>
              <button class="etiqueta" data-etiqueta="yoga">yoga</button>
            </div>
          </div>

          <div class="seccion">
            <h3>Categorías</h3>
            <div class="contenedor-categorias" id="facets">
            <?php $modo="update"; include '../PHP/Servicios/CategoriasInsert.php'?>
            </div>
          </div>

          <div class="seccion">
            <h3>Precio máximo</h3>
            <div class="rango">
              <input type="range" id="precio" min="0" max="10000" step="50" value="10000">
              <span id="precioVal">10000$</span>
            </div>
          </div>
        </aside>

        <!-- Resultados -->
        <section class="panel">
          <div class="controles">
         <label>Ordenar por:</label>
            <select id="orden">
              <option value=""  selected>Recomendados</option>
              <option class="a" value="nombre">Nombre A→Z</option>
              <option value="precio_asc">Mayor Precio</option>
              <option value="precio_desc">Menor Precio</option>
            </select>
            <span id="contador">0 resultados</span>
          </div>

       <div class="resultados" id="resultados">
      <?php $ServiciosMostrar="Proveedores"; include "../PHP/Servicios/Servicios.php"; ?>

</div>
        </section>
      </div>
    </div>

    <!-- Secciones de proveedores -->
    <div class="proveedores-cont">
      <section class="recientes">
        <h1>Cuentas nuevas</h1>
        <div class="contenedor-tarjetas">
          <article class="tarjeta">
            <img src="assets/img/sillon.png">
            <p class="nombre"><strong>Juan Pérez</strong></p>
            <p class="oficio">Desarrollador Frontend</p>
            <a href="">Ver perfil</a>
          </article>

        </div>
      </section>

      <section class="ofertas">
        <h1>En oferta</h1>
        <div class="contenedor-tarjetas">
          <article class="tarjeta-ofertas">
            <img src="../assets/img/sillon.png">
            <p class="nombre-servicio"><strong>Full stack</strong></p>
            <p class="precio"><span id="precio-org">$U9000</span> <span id="precio-oferta">$U7500</span></p>
            <p class="descripcion">Descripción breve del servicio en ofertaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa</p>
            <p class="valoracio">5/5</p>
            <a href="">Ver servicio</a>
          </article>
        </div>
      </section>



      <section class="categoriaMasbuscada">
        <h1>Categorías más buscadas</h1>
        <div class="contenedor-tarjetas">
          <article class="tarjeta">
            <img src="../assets/img/sillon.png">
            <p class="nombre-categoria"><strong>María López</strong></p>
            <button type="button">Ver categoria</button>
          </article>
        


        </div>
      </section>
    </div>
  </main>






  <hr class="hr-general">
  
<?php include UTIL_URL.'footer.php'?>

  <script src="../assets/js/filtros.js"></script>
      <script src="../assets/js/modal.js"></script>
</body>

</html>