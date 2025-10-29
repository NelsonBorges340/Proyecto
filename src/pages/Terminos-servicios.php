<?php
 if (session_status() === PHP_SESSION_NONE) {
    session_start();
}?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>hirenear-terminos y servicios</title>
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
      <h2>Políticas de Seguridad de Hirenear</h2>
      <p>En Hirenear nos tomamos muy en serio la seguridad de nuestros usuarios y proveedores. Implementamos medidas estrictas para proteger la información personal y los datos de transacciones dentro de nuestra plataforma.</p>

      <h3>1. Protección de Datos Personales</h3>
      <p>Todos los datos que recopilemos, incluyendo nombres, correos electrónicos y datos de contacto, serán almacenados de manera segura. No compartiremos esta información con terceros sin el consentimiento explícito del usuario.</p>

      <h3>2. Contraseñas y Autenticación</h3>
      <p>Las contraseñas se almacenan utilizando algoritmos de hash seguros. Recomendamos a todos los usuarios utilizar contraseñas fuertes y únicas, y habilitar autenticación de dos factores cuando sea posible.</p>

      <h3>3. Seguridad de las Transacciones</h3>
      <p>Hirenear utiliza protocolos seguros (HTTPS/SSL) para todas las comunicaciones, garantizando que las transacciones y datos sensibles estén protegidos contra interceptaciones.</p>

      <h3>4. Monitorización y Prevención</h3>
      <p>Contamos con sistemas de monitorización continua para detectar actividad sospechosa y prevenir accesos no autorizados. Cualquier intento de violación de seguridad será investigado y reportado según corresponda.</p>

      <h3>5. Responsabilidades del Usuario</h3>
      <p>Los usuarios son responsables de mantener la confidencialidad de sus credenciales y de reportar cualquier actividad sospechosa en sus cuentas. La cooperación con nuestro equipo de seguridad es fundamental para mantener la plataforma segura.</p>
    </section>
    </main>
      <hr  class="hr-general">
<?php include UTIL_URL.'footer.php'?>
    <script src="../assets/js/modal.js"></script>
</body>
</html>