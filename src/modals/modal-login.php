<?php
$f=false
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>hirenear-modal</title>
  <link rel="shortcut icon" href="assets/img/logo.svg">

  <!-- LINKS FONTS -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap"
    rel="stylesheet">
  <!-- LINKS CSS -->
  <link rel="stylesheet" href="../assets/css/footer-nav-body.css">
  <link rel="stylesheet" href="../assets/css/contact.css">
  <link rel="stylesheet" href="/modals/modal-login-general.css">

</head>

<body>
  <!--
<section class="opt-perfil" style="display: flex; gap: 50px; margin-top: 40px; ">
    <button style="border-radius: 30px; padding: 15px; background-color: black;" data-modal="modal-ajustes">Ajustes</button>
    <button style="border-radius: 30px; padding: 15px; background-color: black;"  data-modal="modal-agregar-servicio">Agregar servicio</button>
    <button style="border-radius: 30px; padding: 15px; background-color: black;" data-modal="modal-editar-servicio">Editar servicio</button>
    <button style="border-radius: 30px; padding: 15px; background-color: black;" data-modal="modal-recuperar-contrasenia">Recuperar contraseña</button>
    <button style="border-radius: 30px; padding: 15px; background-color: black;" data-modal="modal-panel-admin">Panel de Administrador</button>
    <button style="border-radius: 30px; padding: 15px; background-color: black;" data-modal="modal-iniciar">Iniciar Sesión</button>
    <button style="border-radius: 30px; padding: 15px; background-color: black;"  data-modal="modal-registrarse">Registrarse</button>
    <button style="border-radius: 30px; padding: 15px; background-color: black; color: white; border: none; cursor: pointer;" data-modal="modal-ajustar-horarios">Ajustar Horarios</button>
<button style="border-radius: 30px; padding: 15px; background-color: black; color: white; border: none; cursor: pointer;" data-modal="modal-notificaciones">Ver Notificaciones</button>
<button style="border-radius: 30px; padding: 15px; background-color: black; color: white; border: none; cursor: pointer;" data-modal="modal-registrar-servicio">  Registrar Servicio</button>
</section>
-->
  <!-- =============== Modal: Iniciar sesión =============== -->
  <div class="modal-general" id="modal-iniciar" role="dialog" aria-hidden="true">
    <div class="modal-contenido modal-iniciar-sesion">
      <button class="cerrar-modal" aria-label="Cerrar">&times;</button>
      <h1>Iniciar sesión</h1>

      <!--<form method="post" action="/PHP/Usuario/Usuario.php">-->
      <form id="loginForm">
        <div id="loginError" style="color:red;"></div> <!--Styles a esto: es un texto que muestra error de login (usuario no encontrado y contraseña incorrecta)  -->
        <input type="hidden" name="UsuarioLogin" value="1">
        <label for="login-usuario">Tu Correo</label>
        <input name="Correo" type="email" id="login-usuario" placeholder="Ingresa tu Correo" required>

        <label for="login-pass">Tu Contraseña</label>
        <input name="Contraseña" type="password" id="login-pass" placeholder="Ingresa tu contraseña" required>



        <div class="help-inicio-sesion">
          <button id="btn-contrasenia" type="button">¿Olvidaste tu contraseña?</button>
          <p>¿No tienes cuenta?
            <button id="btn-registrarse-en-inicio" type="button">Regístrate</button>
          </p>
        </div>

        <div class="btn-logins">
          <button name="UsuarioLogin" class="button-iniciar" type="submit" style="justify-content: center;">Iniciar sesión</button>
        </div>


      </form>

    </div>
  </div>

  <!-- =============== Modal: Registro =============== -->
  <div class="modal-general" id="modal-registrarse">
    <div class="modal-contenido modal-registrarse">
      <button class="cerrar-modal">&times;</button>
      <h1>Registrarse</h1>

      <form method="post" action="PHP/Usuario/Usuario.php">
        <label>Nombre de usuario</label>
        <input name="Nombre" type="text" required>

        <label>Correo electrónico</label>
        <input name="Correo" type="email" required>

        <label>Teléfono</label>
        <input name="Tel" type="number" required>

        <label>Departamento</label>
        <select name="Ciudad" required>
          <option disabled selected>Selecciona tu departamento...</option>
          <option value="Artigas">Artigas</option>
          <option value="Canelones">Canelones</option>
          <option value="Cerro Largo">Cerro Largo</option>
          <option value="Colonia">Colonia</option>
          <option value="Durazno">Durazno</option>
          <option value="Flores">Flores</option>
          <option value="Florida">Florida</option>
          <option value="Lavalleja">Lavalleja</option>
          <option value="Maldonado">Maldonado</option>
          <option value="Montevideo">Montevideo</option>
          <option value="Paysandu">Paysandú</option>
          <option value="Rio Negro">Río Negro</option>
          <option value="Rivera">Rivera</option>
          <option value="Rocha">Rocha</option>
          <option value="Salto">Salto</option>
          <option value="San Jose">San José</option>
          <option value="Soriano">Soriano</option>
          <option value="Tacuarembo">Tacuarembó</option>
          <option value="Treinta y Tres">Treinta y Tres</option>
        </select>

        <label>Contraseña</label>
        <input name="Contraseña" type="password" required>

        <label>Confirmar contraseña</label>
        <input name="Contraseña2" type="password" required>

        <label for="rol-select">¿Cómo deseas usar Hirenear?</label>
        <select name="Rol" id="rol-select">
          <option value="cliente">Cliente</option>
          <option value="vendedor">Vendedor</option>
        </select>
        <div class="cedula-vendedor" id="cedula-vendedor" style="display:none;">
          <p>Para ser vendedor necesitamos que digites tu cédula, para mas seguridad.</p>
          <input name="Cedula" type="text" placeholder="Número de cédula">
        </div>

        <button name="UsuarioRegistrar" type="submit" class="btn-accion">Crear cuenta</button>
      </form>
    </div>
  </div>

  <!-- =============== Modal: Recuperar contraseña =============== -->
  <div class="modal-general" id="modal-recuperar-contrasenia" role="dialog" aria-hidden="true">
    <div class="modal-contenido modal-recuperar">
      <button class="cerrar-modal" aria-label="Cerrar">&times;</button>
      <h1>¿Olvidaste tu contraseña?</h1>


      <form>
        <label for="recuperar-email">Ingresa tu email</label>
        <input type="email" id="recuperar-email" placeholder="Ingresa tu correo" required>
        <button class="button-iniciar" type="submit">recibir codigo</button>
        <label for="recuperar-codigo">Código recibido</label>
        <input type="text" id="recuperar-codigo" placeholder="Ej: 1234" required>

        <label for="nueva-pass">Nueva contraseña</label>
        <input type="password" id="nueva-pass" placeholder="Nueva contraseña" required>

        <label for="nueva-pass-confirm">Confirmar contraseña</label>
        <input type="password" id="nueva-pass-confirm" placeholder="Confirma tu contraseña" required>

        <div class="btn-logins">
          <button class="button-iniciar" type="submit">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- =============== Modal: Ajustes =============== -->
  <div class="modal-general" id="modal-ajustes">w
    <div class="modal-contenido modal-ajustes">
      <button class="cerrar-modal">&times;</button>
      <div class="ajustes-container">
        <div class="ajustes-menu">
          <button data-panel="perfil" class="active">Información personal</button>
          <!--
        <button data-panel="preferencias">Preferencias de apariencia</button>-->
        <button data-panel="notificaciones">Notificaciones</button>

          <button data-panel="privacidad">Privacidad y seguridad</button>
          <button data-panel="gestion">Gestión de cuenta</button>
          <br><br><br><br><br><br><br><br><br><br><br><br><br>
          <form method="post" action="/PHP/Usuario/Usuario.php"><button name="UsuarioLogout" class="btn-peligro-cerrar">Cerrar Sesión</button></form>

        </div>

        <div class="ajustes-contenido">
          <!-- PERFIL -->
          <div class="panel active" id="perfil">
            <h2>Información personal</h2>
            <form action="/PHP/Usuario/Usuario.php" method="post" enctype="multipart/form-data">
              <div class="input-file-container">
                <label for="foto-perfil" class="btn-file">📷 Cambiar foto</label>

                <input name="foto_perfil" type="file" id="foto-perfil" accept="image/*" hidden>
              </div>
              <label>Nombre</label>
              <input name="Nombre" type="text" id="editar-nombreU" placeholder="Tu nombre" value="<?= htmlspecialchars($DU['NombreUsuario']) ?>">
              <label>Descripción</label>
              <textarea name="Descripcion" id="editar-descripcionU" placeholder="Cuéntanos sobre ti"><?= htmlspecialchars($DU['Descripcion']) ?></textarea>
              <button name="UsuarioActuDatos1" type="submit" class="btn-guardar">Guardar cambios</button>
            </form>
          </div>

          <!-- APARIENCIA -->
          <div class="panel" id="preferencias">
            <h2>Preferencias de apariencia</h2>
            <label>Modo de color</label>
            <select name="tema" id="tema-apariencia">
              <option value="claro">Claro</option>
              <option value="oscuro">Oscuro</option>
            </select>
            <button class="btn-guardar">Aplicar preferencias</button>
          </div>

          <!-- NOTIFICACIONES -->
          <div class="panel" id="notificaciones">
            <h2>Notificaciones</h2>
            <div class="toggle-switch">
              <label for="notif-mensajes">Recibir notificación de mensajes</label>
              <input type="checkbox" id="notif-mensajes" checked>
            </div>
            <div class="toggle-switch">
              <label for="notif-servicios">Recibir notificación de servicios agendados</label>
              <input type="checkbox" id="notif-servicios" checked>
            </div>
            <div class="toggle-switch">
              <label for="notif-email">Recibir notificaciones por correo electrónico</label>
              <input type="checkbox" id="notif-email">
            </div>
            <button class="btn-guardar">Guardar preferencias</button>
          </div>

          <!-- PRIVACIDAD -->
          <div class="panel" id="privacidad">
            <form id="CambiarContraseña" method="post">
              <div id="CambioGood" style="color:Green;"></div>
              <div id="CambioError" style="color:red;"></div>
              <input type="hidden" name="UsuarioCambiarContraseña" value="1">

              <h2>Privacidad y seguridad</h2>
              <h3>Cambiar contraseña</h3>
              <label>contraseña actual</label>
              <input class="PRUEBA" name="contraActual" type="password" placeholder="Tu contraseña">

              <label>contraseña nueva</label>
              <input class="PRUEBA" name="contraNueva" type="password" placeholder="Tu nueva contraseña">

              <button name="UsuarioCambiarContraseña" type="submit" class="btn-accion">Guardar</button>

            </form>


            <div class="help-inicio-sesion">
              <button id="btn-contrasenia" data-modal="modal-recuperar-contrasenia">¿Olvidaste tu contraseña?</button>
            </div>

          </div>

          <!-- GESTIÓN -->
          <div class="panel" id="gestion">
            <h2>Gestión de cuenta</h2>
            <label>Cambiar rol</label>
            <select name="rol" id="rol-select">
              <option value="cliente">Cliente</option>
              <option value="vendedor">Vendedor</option>
            </select>
            <div class="cedula-vendedor" id="cedula-vendedor" style="display:none;">
              <p>Para ser vendedor necesitamos que digites tu cédula, para mas seguridad.</p>
              <input type="text" placeholder="Número de cédula">
            </div>
            <button class="btn-guardar">Guardar cambio de rol</button>
            <form method="post" action="/PHP/Usuario/Usuario.php"><button name="UsuarioEliminar" class="btn-peligro eliminar-cuenta">Eliminar cuenta permanentemente</button></form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- =============== Modal: Agregar servicio =============== -->
  <div class="modal-general" id="modal-agregar-servicio">
    <div class="modal-contenido">
      <button class="cerrar-modal">&times;</button>
      <h1>Agregar Servicio</h1>
      <form action="../PHP/Servicios/Servicios.php" method="post" enctype="multipart/form-data">
        <label>Nombre del servicio</label>
        <input type="text" name="NombreServicio" id="input-nombre-servicio" placeholder="Ej: Diseño de Logo" required>

        <label>Descripción</label>
        <textarea name="Descripcion" id="input-descripcion-servicio" placeholder="Describe el servicio..." required></textarea>

        <label>Precio (pesos uruguayos)</label>
        <input type="number" id="input-precio-servicio" name="Precio" placeholder="Ej: 50" required>

        <label for="select-categoria">Categoria</label>
        <select name="Categoria" id="select-Categoria" required>
          <option value="" disabled selected>Selecciona una categoria</option>
          <?php $modo = "insert";
          include("../PHP/Servicios/CategoriasInsert.php"); ?>
        </select>

        <label>Imagen</label>
        <input type="file" name="Imagen[]" id="input-archivos-servicio" accept="image/*" multiple>
        <div class="previews" id="previews-agregar"></div>

        <button name="ServiciosCrear" type="submit" class="btn-accion">Agregar</button>

      </form>
    </div>
  </div>

  <!-- =============== Modal: Editar servicio =============== -->
  <div class="modal-general" id="modal-editar-servicio">
    <div class="modal-contenido">
      <button class="cerrar-modal">&times;</button>
      <h1>Editar Servicio</h1>
      <form action="../PHP/Servicios/Servicios.php" method="post" id="form-editar-servicio">
        <label>Nombre del servicio</label>
        <input name="nombre" type="text" id="editar-nombre">

        <label>Descripción</label>
        <textarea name="descripcion" id="editar-descripcion"></textarea>

        <label>Precio</label>
        <input name="precio" type="number" id="editar-precio">

        <label>Actualizar imágenes</label>
        <input name="" type="file" id="input-editar-imagen" accept="image/*" multiple>
        <div class="previews" id="previews-editar"></div>

        <input type="text" id="editar-id-servicio" name="id" hidden>
        <button name="ServiciosEditar" type="submit" class="btn-accion">Guardar cambios</button>
      </form>
    </div>
  </div>

  <!-- =============== Modal: Panel Admin =============== -->
  <div class="modal-general" id="modal-panel-admin" role="dialog" aria-hidden="true">
    <div class="modal-contenido modal-admin">
      <button class="cerrar-modal" aria-label="Cerrar">&times;</button>
      <h1>Panel de Administrador</h1>

      <div class="admin-container">
        <div class="admin-menu">
          <button data-panel="usuarios" class="active">👥 Administrar Usuarios</button>
          <button data-panel="servicios">🛠️ Administrar Servicios</button>
          <button data-panel="categorias">🏷️ Administrar Categorías</button>
        </div>

        <div class="admin-contenido">
          <!-- USUARIOS -->
          <div class="panel-admin active" id="usuarios">
            <h3>Usuarios Registrados</h3>

            <div class="buscador-admin">
              <input id="BuscarUsrAdmin" type="text" placeholder="Buscar usuario...">
            </div>
            <div class="lista-usuarios">

              <?php $consulta = true;
              include '../PHP/Usuario/Usuario.php'; ?>
            </div>
          </div>

          <!-- SERVICIOS -->
          <div class="panel-admin" id="servicios">
            <h3>Gestión de Servicios</h3>
            <div class="buscador-admin">
              <input id="BuscarServiciosAdmin" type="text" placeholder="Buscar servicio...">
            </div>



            <div  class="lista-servicios">

              <?php $ServiciosMostrar = "PanelAdmin";
              include '../PHP/Servicios/Servicios.php'; ?>
            </div>
          </div>

          <!-- CATEGORÍAS -->
          <div class="panel-admin" id="categorias">
            <h3>Gestión de Categorías</h3>

            <div class="buscador-admin">
              <input id="q" type="text" placeholder="Buscar categoría...">
              <button class="btn-accion crearCat"> Crear Categoría</button>
            </div>



            <div class="lista-categorias">

              <?php $ServiciosMostrar = "Categorias";
              include '../PHP/Categoria/Categoria.php'; ?>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ================== Modal: Ajustar horarios ================== -->
  <div class="modal-general" id="modal-ajustar-horarios">
    <div class="modal-contenido">
      <button class="cerrar-modal">&times;</button>
      <h1>Ajusta tus horarios</h1>
      <p>Configura los horarios para que tus clientes puedan agendarse.</p>

      <form id="form-horarios">
        <div class="dia-horario">
          <label>Lunes:</label>
          <input type="time" name="lunes-desde"> a <input type="time" name="lunes-hasta">
        </div>
        <div class="dia-horario">
          <label>Martes:</label>
          <input type="time" name="martes-desde"> a <input type="time" name="martes-hasta">
        </div>
        <div class="dia-horario">
          <label>Miércoles:</label>
          <input type="time" name="miercoles-desde"> a <input type="time" name="miercoles-hasta">
        </div>
        <div class="dia-horario">
          <label>Jueves:</label>
          <input type="time" name="jueves-desde"> a <input type="time" name="jueves-hasta">
        </div>
        <div class="dia-horario">
          <label>Viernes:</label>
          <input type="time" name="viernes-desde"> a <input type="time" name="viernes-hasta">
        </div>
        <div class="dia-horario">
          <label>Sábado:</label>
          <input type="time" name="sabado-desde"> a <input type="time" name="sabado-hasta">
        </div>
        <div class="dia-horario">
          <label>Domingo:</label>
          <input type="time" name="domingo-desde"> a <input type="time" name="domingo-hasta">
        </div>

        <button type="submit" class="btn-accion">Guardar horarios</button>
      </form>
    </div>
  </div>

  <!-- ================== Modal: Notificaciones ================== -->
  <div class="modal-general" id="modal-notificaciones">
    <div class="modal-contenido">
      <button class="cerrar-modal">&times;</button>
      <h1>Notificaciones</h1>

      <div class="lista-notificaciones">

        <?php $ServiciosMostrar = "MostrarNoticificaciones"; 
        include __DIR__ . '/../PHP/Servicios/Servicios.php'; ?>

      </div>
    </div>
  </div>

  <!-- ================== Modal: Registrar servicio (para clientes) ================== -->
  <div class="modal-general" id="modal-registrar-servicio">
    <div class="modal-contenido">
      <button class="cerrar-modal">&times;</button>
      <h1>Registrar Servicio</h1>
      <p>Fecha seleccionada: <span id="fecha-seleccionada">01/01/2025</span></p>

      <form id="form-registrar-servicio">
        <label>Nombre del servicio</label>
        <select>
          <option value="">Selecciona un servicio</option>
          <option value="diseño">Diseño de Logo</option>
          <option value="electricidad">Trabajo de Electricidad</option>
          <option value="asesoria">Asesoría</option>
        </select>

        <label>Hora deseada</label>
        <input type="time" name="hora">

        <button type="submit" class="btn-accion">Agendar</button>
      </form>
    </div>
  </div>
  <!-- =============== Modal: Agregar Categoria =============== -->
  <div class="modal-general" id="modal-agregar-Categoria">
    <div class="modal-contenido">
      <button class="cerrar-modal">&times;</button>
      <h1>Agregar Categoria</h1>
      <form action="../PHP/Categoria/Categoria.php" method="post" enctype="multipart/form-data">
        <label>Nombre de Categoria</label>
        <input type="text" name="NombreServicio" id="input-nombre-servicio" placeholder="Ej: Diseño de Logo" required>
        <button name="ServiciosCrear" type="submit" class="btn-accion">Agregar</button>

      </form>
    </div>
  </div>

  <script src="../assets/js/modal.js"></script>

</body>

</html>