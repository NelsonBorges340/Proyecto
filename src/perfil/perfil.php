<?php
 if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
    if (!isset($_SESSION['usuario'])) {
    header("Location: /index.php");
    exit();
}

      $consulta="Mostrar";
       include '../PHP/Usuario/Usuario.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
       <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>hirenear-perfil</title>
    <link rel="shortcut icon" href="../assets/img/logo.svg">

    <!-- LINKS FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap"
        rel="stylesheet">
    <!-- LINKS CSS -->
    <link rel="stylesheet" href="perfil.css">
    <link rel="stylesheet" href="../assets/css/footer-nav-body.css">
    <link rel="stylesheet" href="/modals/modal-login-general.css">

</head>

<body>

<?php include '../Direcciones.php'; include UTIL_URL.'header.php'; ?>

    
    <hr class="hr-general">


<main>

  <aside class="aside">
    <div class="hero-perfil">
      <a data-modal="modal-notificaciones">></a>
      <img src="<?= htmlspecialchars($DU['Foto_perfil'] ?? 'https://i.pravatar.cc/90?img=1') ?>" alt="Foto de perfil">
      <h1><?= htmlspecialchars($DU['NombreUsuario']) ?></h1>
      <p><strong></strong></p>

<p><?= !empty($DU['Descripcion']) ? htmlspecialchars($DU['Descripcion']) : '' ?></p>

      <div class="puntuacion-estrellas">
        <span class="estrellas">★★★★☆</span>
        <span class="promedio">4.5 / 5.0</span>
      </div>
    </div>

    <section class="opt-perfil">
      <button data-modal="modal-ajustes" >Ajustes</button>
      <?php if (!empty($_SESSION['usuario']) && $_SESSION['tipo'] != "cliente" ): ?>
      <button data-modal="modal-agregar-servicio">Agregar servicio</button>
      <button data-modal="modal-ajustar-horarios">Ajustar Disponibilidad</button>
      <?php endif; ?>
      <?php if (!empty($_SESSION['usuario']) && $_SESSION['tipo'] == "admin" ): ?>
      <button data-modal="modal-panel-admin">Panel de Administrador</button>
      <?php endif; ?>
    </section>
  </aside>

<?php if (!empty($_SESSION['usuario']) && $_SESSION['tipo'] != "cliente"): ?>
  <section class="content">


    <div class="agenda-cta">
      <h2>Agendar Servicio</h2>
      <div class="calendario">
        <div class="calendario-header">
          <button class="nav-month" data-nav="prev">←</button>
          <span class="month-display">Octubre 2025</span>
          <button class="nav-month" data-nav="next">→</button>
        </div>
        <div class="dias-semana">
          <span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span>
        </div>
        <div class="dias-mes">
          <span class="vacio"></span><span class="vacio"></span><span class="dia actual">1</span>
          <span class="dia">2</span><span class="dia">3</span><span class="dia">4</span><span class="dia">5</span>
        </div>
      </div>
      <p class="nota-agenda">Selecciona un día disponible para ver horarios.</p>
    </div>


    <h2>✨ Mis Servicios</h2>

    <div class="servicios-wrapper">
      <div class="servicios">

        <!--<div class="servicio-card">
          <img src="https://picsum.photos/250/160?random=2" alt="Desarrollo Web">
          <div class="servicio-info">
            <h3>Desarrollo Web</h3>
            <p>Sitio web a medida y optimizado.</p>
            <p class="precio">Precio: <span>$250</span></p>
          </div>
          <div class="servicio-botones">
            <button>Ver</button>
            <button>Editar</button>
            <button>Eliminar</button>
              -->
            
          <?php $ServiciosMostrar=true; include "../PHP/Servicios/Servicios.php"; ?>
    
        
      </div>
    </div>

  



    <section class="resenias">
      <h2>Comentarios</h2>
      <div class="subir-coment">
        <input type="text" placeholder="Agrega un comentario...">
        <button>Enviar</button>
      </div>
      <div class="comentarios">

        <div class="comentario">
          <img src="https://i.pravatar.cc/40?img=2" alt="Mauro">
          <div><h1>Mauro</h1><p>Muy profesional 👏</p></div>
        </div>

        <div class="comentario">
          <img src="https://i.pravatar.cc/40?img=3" alt="Lucía">
          <div><h1>Lucía</h1><p>Excelente trabajo!</p></div>
        </div>

        <div class="comentario">
          <img src="https://i.pravatar.cc/40?img=4" alt="Carlos">
          <div><h1>Carlos</h1><p>Recomendado 100%</p></div>
        </div>

      </div>
    </section>

      <?php endif; ?>
  </section>
</main>





    <hr class="hr-general">
    <footer>
        <div class="footer-content">
            <ul>
                <li><a href="../pages/about.html">Acerca de</a></li>
                <li><a href="../pages/contact.html">Contacto</a></li>
                <li><a href="../pages/Terminos-servicios.html">Términos de Servicio</a></li>
                <li><a href="../pages/proveedores.html">Política de Privacidad</a></li>
                <li><a href="">Ayuda</a></li>
            </ul>
            <div class="footer-sect-contacto">
                <h1>Contacto</h1>
                <p>Email: Rabatechdevs@gmail.com</p>
                <p>Telefono: xxx xxx xxx</p>
            </div>
            <div class="footer-sect-aside">
                <a href=""><img src="../assets/img/icons8-facebook-color/icons8-facebook-48.png"></a>
                <a href=""><img src="../assets/img/icons8-instagram-windows-11-color/icons8-instagram-50.png"></a>
                <a href=""><img src="../assets/img/icons8-twitterx-ios-17-filled/icons8-twitterx-50.png" alt=""></a>
            </div>
        </div>



        <div class="footer-bottom">
            <p>
                &copy; <time datetime="2025">2025</time> RabaTech. Todos los derechos
                reservados.
            </p>
        </div>

    </footer>
<?php include MODA_URL?>
<script src="../assets/js/calendario.js"></script>
<script src="camentario-valoracion.js"></script>
<script src="../assets/js/modal.js"></script>
<script src="EliminarServicio.js"></script>
<script src="../assets/js/prueba.js"></script>
<script src="../assets/js/filtros.js"></script>
</body>

</html>