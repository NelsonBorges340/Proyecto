<?php


if (isset($_SESSION['usuario'])): ?>
<?php
    $consulta="Mostrar";
       include __DIR__.'/../Usuario/Usuario.php';
    ?>
    <nav>
        <div class="logo"><a href="../index.php"><img src="../assets/img/logo.svg" alt=""></a>
            <h1>Hirenear</h1>
        </div>

        <ul>
            <li><a href="/index.php">Inicio</a></li>
            <li><a href="/pages/proveedores.php">Proveedores</a></li>
            <hr>
            <li><a href="/pages/about.php">Sobre Nosotros</a></li>



        </ul>

        <!-- cuando inicias cambia esta seccion de nav icons-->
        <div class="nav-icons">
            <a class="filro" data-modal="modal-notificaciones"> <img src="../assets/img/notificacion.png" alt="notificaciones"> </a>
            <a class="filro" href="/chat/chat.php"> <img src="../assets/img/chat.png" alt="chat"></a>
            <button class="filro-button"><img src="../assets/img/icons8-marcador-undefined/icons8-marcador-50.png" alt="ubicacion"></button>
            <a href="/perfil/perfil.php"> <img src="<?= htmlspecialchars($DU['Foto_perfil'] ?? 'https://i.pravatar.cc/90?img=1') ?>" alt="Foto de perfil"></a>
                
        </div>
        
    </nav>

<?php else: ?>

      <nav>
        
        <link rel="stylesheet" href="/modals/modal-login-general.css">

        <div class="logo"><a href="/index.php"><img src="/assets/img/logo.svg" alt=""></a>
            <a href="/index.php"><h1>Hirenear</h1></a>
        </div>

        <ul>
          
          
            <li><a href="/index.php">Inicio</a></li>
            <li><a href="/pages/proveedores.php">Proveedores</a></li>
            <hr>
            <li><a href="/pages/about.php">Sobre Nosotros</a></li>
        </ul>

        <div class="btn-nav">
            <button data-modal="modal-iniciar" id="btn-iniciar">Iniciar sesion</button>
            <button data-modal="modal-registrarse" id="btn-registrarse">Registrarse</button>
        </div>
        
        <?php include MODA_URL?>
      </nav>

<?php endif; ?>

