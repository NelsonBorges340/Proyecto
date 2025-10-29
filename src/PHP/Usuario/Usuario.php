<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



require_once "consultas-usuario.php";
require_once __DIR__ . "/../Conexion.php";
$Usr = new usuario($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['UsuarioRegistrar'])) {


        $Nombre = $_POST['Nombre'];
        $Correo = $_POST['Correo'];
        $Contraseña = $_POST['Contraseña'];
        $Contraseña2 = $_POST['Contraseña2'];
        $Tel = $_POST['Tel'];
        $Ciudad = $_POST['Ciudad'];
        $Rol = $_POST['Rol'];
        $Cedula = $_POST['Cedula'];


        if ($Contraseña !== $Contraseña2) {
            // Las contraseñas no coinciden, redirigir o mostrar error
            header("Location: /index.php?error=contrasena");
            exit();
        }
        // Validar que los campos no estén vacíos (puedes agregar validaciones aquí)
        $claveHash = password_hash($Contraseña, PASSWORD_DEFAULT);

        // Verificar si el correo ya existe
        $stmt = $pdo->prepare("SELECT Correo FROM Usuario WHERE Correo = ?");
        $stmt->execute([$Correo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Si el correo ya existe, redirigir o mostrar error
            // Use a web path (not filesystem __DIR__)
            header("Location: /index.php?error=correo");
            exit();
        } else {
            // Insertar el nuevo usuario
            $stmt = $pdo->prepare("INSERT INTO Usuario (NombreUsuario, Correo, Contraseña,Telefono, CI, Tipo_usuario) VALUES (?, ?, ?, ?,?,?)");
            $result = $stmt->execute([$Nombre, $Correo, $claveHash, $Tel, $Cedula, $Rol]);

            if ($result) {

                // Registro exitoso: redirigir a la página principal
                header("Location: /index.php");
                exit();
            } else {
                echo "Error: No se pudo registrar el usuario.";
            }
        }
    }
    if (isset($_POST['UsuarioLogin'])) {

        require_once "consultas-usuario.php";
        $Correo = $_POST['Correo'] ?? '';
        $Contraseña = $_POST['Contraseña'] ?? '';


        $usuarioObj = new Usuario($pdo);
        $row = $usuarioObj->buscarPorCorreo($Correo);

        if ($row && password_verify($Contraseña, $row['Contraseña'])) {
            // iniciar sesión
            $_SESSION['usuario'] = $row['IDusuario'];
            $_SESSION['nombre'] = $row['NombreUsuario'];
            $_SESSION['tipo'] = $row['Tipo_usuario'];

            // Solo responde JSON, no redirige
            echo json_encode(['success' => true]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $row ? 'Contraseña incorrecta.' : 'Usuario no encontrado.'
            ]);
        }
        exit(); // Termina aquí para no seguir ejecutando el resto del script
    }

    if (isset($_POST['UsuarioLogout'])) {
        $_SESSION = [];
        session_destroy();  // Elimina todos los datos de sesión
        // Redirigir al usuario a la página de inicio
        header("Location: " . '/index.php');
        exit();
    }

    if (isset($_POST['UsuarioEliminar'])) {
        $IDusuario = $_SESSION['usuario'];
        $stmt = $pdo->prepare("DELETE FROM Usuario WHERE IDusuario = ?");
        $stmt->execute([$IDusuario]);

        // Cerrar sesión y redirigir
        session_destroy();
        header("Location: /index.php");
        exit;
    }

    if (isset($_POST['UsuarioEliminarAdmin'])) {
        $IDusuario = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM Usuario WHERE IDusuario = ?");
        $stmt->execute([$IDusuario]);

        exit;
    }


    if (isset($_POST['UsuarioCambiarContraseña'])) {
        header('Content-Type: application/json');

        $row = $Usr->buscarPorID($_SESSION['usuario']);

        $pws = $_POST['contraActual'] ?? '';
        $Npws = $_POST['contraNueva'] ?? '';

        if ($pws && $Npws) {

            if (password_verify($pws, $row['Contraseña'])) {
                $hashNueva = password_hash($Npws, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE Usuario SET Contraseña = ? WHERE IDusuario = ?");
                $stmt->execute([$hashNueva, $_SESSION['usuario']]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Contraseña actualizada con éxito.'
                    ]);
                } else {

                    echo json_encode([
                        'success' => false,
                        'message' => "No se pudo actualizar la contraseña."
                    ]);
                }
            } else {

                echo json_encode([
                    'success' => false,
                    'message' => "La contraseña actual es incorrecta."
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Por favor completa ambos campos."
            ]);
        }
        exit;
    }

    if (isset($_POST['UsuarioActuDatos1'])) {


        $usuario_id = $_SESSION['usuario'];
        $Nombre = $_POST['Nombre'];
        $Descripcion = $_POST['Descripcion'];

        try {
            $pdo->beginTransaction();

            // Actualizar datos del usuario
            $stmt = $pdo->prepare("UPDATE Usuario SET NombreUsuario=?, Descripcion=? WHERE IDusuario = ?");
            $stmt->execute([$Nombre, $Descripcion, $usuario_id]);

            // Procesar la foto si se subió una
            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                $archivo = $_FILES['foto_perfil'];

                // Validar el archivo
                $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
                $tamaño_maximo = 5 * 1024 * 1024; // 5MB

                if (!in_array($archivo['type'], $tipos_permitidos)) {
                    throw new Exception('Tipo de archivo no permitido');
                }

                if ($archivo['size'] > $tamaño_maximo) {
                    throw new Exception('El archivo es demasiado grande');
                }

                // Verificar y eliminar foto anterior
                $sql_antigua = "SELECT Foto_perfil FROM Usuario WHERE IDusuario = ?";
                $stmt_antigua = $pdo->prepare($sql_antigua);
                $stmt_antigua->execute([$usuario_id]);
                $foto_antigua = $stmt_antigua->fetchColumn();

                if ($foto_antigua) {
                    $ruta_antigua = $_SERVER['DOCUMENT_ROOT'] . $foto_antigua;
                    if (file_exists($ruta_antigua)) {
                        unlink($ruta_antigua);
                    }
                }

                // Crear directorio si no existe
                $directorio_destino = $_SERVER['DOCUMENT_ROOT'] . '/uploads/perfil/';
                if (!file_exists($directorio_destino)) {
                    mkdir($directorio_destino, 0777, true);
                }

                // Generar nombre único para el archivo
                $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
                $nombre_archivo = 'perfil_' . $usuario_id . '.' . $extension;
                $ruta_archivo = $directorio_destino . $nombre_archivo;

                // Mover archivo y actualizar BD
                if (move_uploaded_file($archivo['tmp_name'], $ruta_archivo)) {
                    $ruta_bd = '/uploads/perfil/' . $nombre_archivo;
                    $stmt = $pdo->prepare("UPDATE Usuario SET Foto_perfil = ? WHERE IDusuario = ?");
                    $stmt->execute([$ruta_bd, $usuario_id]);
                }
            }

            $pdo->commit();

            header("Location: /perfil/perfil.php");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            header("Location: /perfil/perfil.php?error=" . urlencode($e->getMessage()));
            exit();
        }
    }


    if (isset($_POST['UsuarioEditar'])) {

        $idusr = $_POST['id'] ?? null;
        $NombreUsr = $_POST['Nombre'] ?? null;
        $CorreoUsr = $_POST['Correo'] ?? null;
        $TelUsr = $_POST['Telefono'] ?? null;
        $CedlaUsr = $_POST['CI'] ?? null;

        if ($idusr && $NombreUsr && $CorreoUsr && $TelUsr) {
            $stmt = $pdo->prepare("UPDATE Usuario SET NombreUsuario = ?, Correo = ?, Telefono = ?, CI = ? WHERE IDusuario = ?");
            $stmt->execute([$NombreUsr, $CorreoUsr, $TelUsr, $CedlaUsr, $idusr]);

            echo json_encode(['success' => true]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error.'
            ]);
        }
        exit();
    }
}


if ($consulta === true) {
    require_once __DIR__ . '/../Conexion.php';

    $usuario = new Usuario($pdo);
    $usuarios = $usuario->Usuarios();

    foreach ($usuarios as $usuarios) {
?>
        <div class="buscador-admin usuario-item "
            data-idusr="<?= $usuarios['IDusuario'] ?>"
            data-nombre="<?= htmlspecialchars($usuarios['NombreUsuario']) ?>">
            <article class="usuario">
                <img src="https://i.pravatar.cc/50?img=2" alt="<?= htmlspecialchars($usuarios['NombreUsuario']) ?>">
                <p><strong>Nombre:</strong> <input type="text" name="NombreUsr" value="<?= htmlspecialchars($usuarios['NombreUsuario']) ?>"> </p>
                <p><strong>Rol:</strong> <?= htmlspecialchars($usuarios['Tipo_usuario']) ?></p>
                <p><strong>Cédula:</strong> <input maxlength="8" type="text" name="CedlaUsr" value=" <?= htmlspecialchars($usuarios['CI']) ?>"></p>
                <p><strong>Correo:</strong> <input type="text" name="CorreoUsr" value="<?= htmlspecialchars($usuarios['Correo']) ?>"></p>
                <p><strong>Telefono:</strong> <input maxlength="9" type="text" name="TelUsr" value="<?= htmlspecialchars($usuarios['Telefono']) ?>"></p>
                <p><strong>Depata.:</strong> <?= htmlspecialchars($usuarios['Departamento']) ?></p>
                <button data-idusr="<?= $usuarios['IDusuario'] ?>" class="btn-accion updusr">Guardar</button>
                <button data-idusr="<?= $usuarios['IDusuario'] ?>" class="btn-peligro delusr">X</button>
            </article>
        </div>
<?php

    }
}

if ($consulta === "Mostrar") {
    $DU = $Usr->buscarPorID($_SESSION['usuario']);
}
