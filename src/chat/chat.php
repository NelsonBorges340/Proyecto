<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>hirenear-chat</title>
    <link rel="shortcut icon" href="../assets/img/logo.svg">

    <!-- LINKS FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap"
        rel="stylesheet">
    <!-- LINKS CSS -->
    <link rel="stylesheet" href="chat2.css">
    
    <link rel="stylesheet" href="../assets/css/footer-nav-body.css">
    
</head>

<body>

  <?php include '../Direcciones.php'; include UTIL_URL.'header.php'; ?>


    <hr class="hr-general">


    
  <div class="chat-centrado">
    <div class="chat">

      <!-- Panel Izquierdo -->
      <div class="panel-izq">
 <div class="lista-contactos" id="listaContactos">

      <?php $_POST['Select']="RecibirNuevoChat"; include (__DIR__."/../PHP/Chat/Chat.php");?>
      <?php $_POST['Select']="RecibirUsuarios"; include (__DIR__."/../PHP/Chat/Chat.php");?>
      </div>
    </div>

      <!-- Panel Derecho -->
      <div class="panel-der">
        <div class="encabezado">
          <img id="imagenChat">
          <div class="nombre-chat" id="nombreChat">Selecciona un contacto</div>
        </div>

        <div class="mensajes" id="mensajesChat"></div>
          
        <div class="input-area">
          <input type="text" id="entradaMensaje" placeholder="Escribe un mensaje...">
          <button type="button" id="botonEnviar" class="material-icons">enviar</button>
        </div>
      </div>

    </div>
  </div>





    <hr class="hr-general">
   

<script src="../assets/js/calendario.js"></script>
<script src="chat.js"></script>
</body>

</html>