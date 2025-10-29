<?php
 if (session_status() === PHP_SESSION_NONE) {
    session_start();
}?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>hirenear-Politicas de seguridad</title>
    <link rel="shortcut icon" href="../assets/img/logo.svg">

    <!-- LINKS FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap"
        rel="stylesheet">
    <!-- LINKS CSS -->
    <link rel="stylesheet" href="../assets/css/footer-nav-body.css">
    <link rel="stylesheet" href="../assets/css/general-styles.css">
         <link rel="stylesheet" href="../assets/css/modal-login-general.css">
         <?php include_once '../Direcciones.php'?>
</head>
<body>
        <!--header-->
        <?php include UTIL_URL.'header.php'; ?>
        
      <hr  class="hr-general">
        <main class="main-content">
       <section class="section">
      <h2>Términos de Servicio de Hirenear</h2>
      <p>Estos términos regulan el uso de la plataforma Hirenear por parte de usuarios y proveedores. Al utilizar Hirenear, aceptas cumplir con estas condiciones.</p>

      <h3>1. Registro y Uso de la Cuenta</h3>
      <p>Los usuarios deben registrarse con información veraz y actualizada. Cada cuenta es personal e intransferible. Hirenear se reserva el derecho de suspender cuentas que infrinjan estas condiciones.</p>

      <h3>2. Contenido y Servicios</h3>
      <p>Los proveedores son responsables del contenido que publican, incluyendo descripciones, precios y servicios ofrecidos. Hirenear no se hace responsable por discrepancias entre lo publicado y lo ofrecido.</p>

      <h3>3. Prohibiciones</h3>
      <ul>
        <li>No se permite el uso de la plataforma para fines ilegales o fraudulentos.</li>
        <li>No se permite publicar contenido ofensivo, difamatorio o que viole derechos de terceros.</li>
        <li>Está prohibido intentar acceder o interferir con los sistemas de Hirenear de manera no autorizada.</li>
      </ul>

      <h3>4. Responsabilidad Limitada</h3>
      <p>Hirenear proporciona la plataforma “tal cual” y no garantiza la exactitud, disponibilidad o continuidad de los servicios. La empresa no será responsable por daños directos o indirectos derivados del uso de la plataforma.</p>

      <h3>5. Modificaciones de los Términos</h3>
      <p>Hirenear se reserva el derecho de modificar estas políticas y términos en cualquier momento. Los cambios se publicarán en esta página y se recomienda a los usuarios revisarla periódicamente.</p>

      <h3>6. Legislación Aplicable</h3>
      <p>Estos términos se rigen por la legislación vigente del país donde opera Hirenear. Cualquier disputa se resolverá ante los tribunales competentes en dicha jurisdicción.</p>

      <p class="nota">Fecha de última actualización: 14 de octubre de 2025</p>
    </section>
</main>
      <hr  class="hr-general">
<?php include UTIL_URL.'footer.php'?>
        <script src="../assets/js/modal.js"></script>
</body>
</html>