<?php
 if (session_status() === PHP_SESSION_NONE) {
    session_start();
}?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>hirenear-contacto</title>
    <link rel="shortcut icon" href="../assets/img/logo.svg">

    <!-- LINKS FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap"
        rel="stylesheet">
    <!-- LINKS CSS -->
    <link rel="stylesheet" href="../assets/css/footer-nav-body.css">
    <link rel="stylesheet" href="../assets/css/contact.css">
        <link rel="stylesheet" href="../assets/css/modal-login-general.css">
        <?php include_once '../Direcciones.php'?>
</head>

<body>
        <!--header-->
        <?php include UTIL_URL.'header.php'; ?>
        
    <hr class="hr-general">

    <div class="contacto-content">
        <p>Ponte en contacto con nuestro equipo!</p>
        <form action="">
            <div class="efect-contact"><hr> <label for="">Nombre completo</label></div>
             <input type="text" placeholder="Escribre tu nombre">

            <div class="efect-contact">
                <hr> <label for="">Numero de Telefono</label>
            </div>
            <input type="number" placeholder="Escribre tu Numero Telefonico">

            <div class="efect-contact">
                <hr> <label for="">Email</label>
            </div>
            <input type="email" placeholder="Escribre tu email ">

            <div class="efect-contact">
                <hr> <label for="">Escribe tu consulta </label>
            </div>
            <textarea name="" id="" placeholder="Escribe tu mensaje aqui."></textarea>
        </form>
        <button class="enviar">enviar</button>
    </div>

  <hr  class="hr-general">
<?php include UTIL_URL.'footer.php'?>
    <script src="../assets/js/modal.js"></script>
</body>

</html>