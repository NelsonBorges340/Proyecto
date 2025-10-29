<?php
 if (session_status() === PHP_SESSION_NONE) {
    session_start();
}?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>hirenear-bienvenido</title>
    <link rel="shortcut icon" href="assets/img/logo.svg">

    <!-- LINKS FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap"
        rel="stylesheet">
    <!-- LINKS CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/footer-nav-body.css">
        <link rel="stylesheet" href="/modals/modal-login-general.css">
    <?php include_once 'Direcciones.php'?>
</head>

<body>
        <?php include_once UTIL_URL.'header.php'; ?>


    <hr class="hr-general">
    <div class="hero-content">
        <section class="hero-section">
            <h1>Encuentra servicios en minutos</h1>
            <p>Conéctate con profesionales capacitados para todas tus necesidades,<br>
                desde reparaciones del hogar hasta cuidado personal.</p>
            <div class="hero-search">
                <input type="search" placeholder="Busca servicios o proveedores" />
                <button><img src="assets/img/lupa-de-busqueda.png" alt=""></button>
            </div>

        </section>
    </div>
<main>
    <div class="opciones-servicios">
        <div class="opciones">
            <a href="" id="categorias1">Desarrollo y Tecnología</a>
        </div>
        <div class="opciones">
            <a href="" id="categorias2">Diseño y Creatividad</a>
        </div>
        <div class="opciones">
            <a href="" id="categorias3">Arte y Manualidades</a>
        </div>
        <div class="opciones">
            <a href="" id="categorias4">Servicios Personales</a>
        </div>
        <div class="opciones">
            <a href=""id="categorias5"> Hogar y Reparaciones</a>
        </div>
        <div class="opciones">
            <a href="" id="categorias6">Educación y Capacitación</a>
        </div>
         <div class="opciones">
            <a href="" id="categorias7">Transporte y Logística</a>
        </div>
    </div>

<h1 class="mas-valorados-title">Los Más Valorados⭐</h1>

<section class="mas-valorados">
  <div class="tarjeta">
    <img src="assets/img/sillon.png" alt="Usuario 1">
    <p><strong>Juan Pérez</strong></p>
    <p id="oficio">Desarrollador Frontend</p>
    <!-- el emoji ☆ tiene que estar en cada valoracion siempre antes del numero (lo haces con php) -->
    <p id="valoracion">☆5.4</p>
    <a href="">Ver perfil</a>
  </div>

  <div class="tarjeta">
    <img src="assets/img/sillon.png" alt="Usuario 2">
    <p><strong>María López</strong></p>
       <p id="oficio">Desarrollador Frontend</p>
         <p id="valoracion">☆5.4</p>
    <a href="">Ver perfil</a>
  </div>

  <div class="tarjeta">
    <img src="assets/img/sillon.png" alt="Usuario 3">
    <p><strong>Axel Sellanes</strong></p>
       <p id="oficio">Desarrollador Frontend</p>
         <p id="valoracion">☆5.4</p>
    <a href="">Ver perfil</a>
  </div>

  <div class="tarjeta">
    <img src="assets/img/sillon.png" alt="Usuario 4">
    <p><strong>Leandro Artigas</strong></p>
    <p id="oficio">Desarrollador Frontend</p>
      <p id="valoracion">☆5.4</p>
    <a href="">Ver perfil</a>
  </div>
</section>




<section class="hero-categorias">
  <h1>Explora Categorías</h1>
  <div class="explora-categorias">
    <a href="#">Desarrollador Web</a>
    <a href="#">Desarrollador de apps móviles</a>
    <a href="#">Diseñador gráfico</a>
    <a href="#">Animador 2D/3D</a>
    <a href="#">Ilustrador</a>
    <a href="#">Fotógrafo</a>
    <a href="#">Editor de video</a>
    <a href="#">Community Manager</a>
    <a href="#">Pintor</a>
    <a href="#">Escultor</a>
    <a href="#">Carpintero</a>
    <a href="#">Electricista</a>
    <a href="#">Plomero</a>
    <a href="#">Jardinería</a>
    <a href="#">Masajista</a>
    <a href="#">Entrenador Personal</a>
    <a href="#">Estilista</a>
    <a href="#">Maquillador</a>
    <a href="#">Profesor Particular</a>
    <a href="#">Traductor</a>
  </div>
</section>


   <section class="about-hirenear-section">
    <h2>Sobre Nosotros</h2>
  <p><strong>Hirenear</strong> es una plataforma innovadora diseñada para conectar a profesionales con personas que buscan servicios de calidad de manera rápida y segura.</p>

  <p>Nuestro objetivo es brindar un espacio confiable donde los vendedores puedan mostrar sus habilidades, servicios y proyectos, mientras que los clientes encuentran soluciones a sus necesidades de manera eficiente.</p>

  <p>En <strong>Hirenear</strong> nos enfocamos en la transparencia y la organización: diferenciamos claramente entre clientes y proveedores, fomentando la comunicación directa y profesional entre ambas partes.</p>

  <p>Con nuestra plataforma, buscamos transformar la manera en que se ofrecen y se encuentran servicios, facilitando la interacción, aumentando la visibilidad de los profesionales y ofreciendo una experiencia segura y efectiva para todos los usuarios.</p>
</section>


</main>
    <hr class="hr-general">

    <?php include UTIL_URL.'footer.php'?>

<script src="assets/js/modal.js"></script>
         
</body>

</html>