<?php
 if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__.'/../PHP/Conexion.php';
require_once __DIR__.'/../PHP/Usuario/consultas-usuario.php';

$id = $_GET['id'] ?? null;
$DU = null;

if ($id) {
    $Usr = new Usuario($pdo);
    $DU = $Usr->buscarPorID($id);
}

if (!$DU) {
    echo "Usuario no encontrado.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
       <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= htmlspecialchars($DU['NombreUsuario']) ?></title>
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
      <img src="https://i.pravatar.cc/90?img=1" alt="Foto de perfil">
      <h1><?= htmlspecialchars($DU['NombreUsuario']) ?></h1>
      <p><strong></strong></p>
<p><?= !empty($DU['Descripcion']) ? htmlspecialchars($DU['Descripcion']) : '' ?></p>

      <div class="puntuacion-estrellas">
        <span class="estrellas">★★★★☆</span>
        <span class="promedio">4.5 / 5.0</span>
      </div>
    </div>

  </aside>


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
            
          <?php $ServiciosMostrar=true; $_POST['id'] = $DU['IDusuario']; include "../PHP/Servicios/Servicios.php"; ?>
    
        
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


  </section>
</main>





    <hr class="hr-general">
   
 <?php include UTIL_URL.'footer.php'?>

<?php include MODA_URL?>
<script src="../assets/js/calendario.js"></script>
<script src="camentario-valoracion.js"></script>
<script src="../assets/js/modal.js"></script>
<script src="EliminarServicio.js"></script>


</body>

</html>