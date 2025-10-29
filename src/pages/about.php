<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
}?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>hirenear-Sobre nosotros</title>
    <link rel="shortcut icon" href="../assets/img/logo.svg">

    <!-- LINKS FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap"
        rel="stylesheet">
    <!-- LINKS CSS -->
    <link rel="stylesheet" href="../assets/css/footer-nav-body.css">
    <link rel="stylesheet" href="../assets/css/about.css">
      <link rel="stylesheet" href="../assets/css/modal-login-general.css">
</head>

<body>
        <!--header-->
        <?php include '../Direcciones.php';
        include UTIL_URL.'header.php'; ?>

    <hr class="hr-general">

    <section id="about-us" class="about-us-section">
        <h2>Sobre Nosotros</h2>
        <p>Somos estudiantes comprometidos con nuestro proyecto final.</p>

        <p>Nuestro equipo está formado por:</p>

        <ul>
            <li><strong>Janio Cáceres</strong> - Encargado de bases de datos</li>
            <li><strong>Axel Sellanes</strong> - Administración y gestión del proyecto</li>
            <li><strong>Leandro Artigas</strong> - Desarrollo backend en PHP</li>
            <li><strong>Maximiliano Lacuesta</strong> - Desarrollo frontend y diseño</li>
        </ul>

        <p>
            Juntos hemos creado <strong>Hirenear</strong>, una plataforma innovadora
            de búsqueda de empleo que conecta a usuarios con oportunidades laborales
            de manera simple y eficiente.
        </p>
    </section>


    <hr class="hr-general">
   <?php include UTIL_URL.'footer.php'?>
    <script src="../assets/js/modal.js"></script>
</body>

</html>