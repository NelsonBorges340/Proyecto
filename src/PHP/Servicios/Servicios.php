<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . "/../../Direcciones.php";
    require_once __DIR__ . "/../Conexion.php";
    require_once __DIR__ . "/consultas-Servicios.php";
    require_once __DIR__ . "/../Usuario/consultas-usuario.php";


    if (isset($_POST['ServiciosCrear'])) {

        $Nombre = $_POST['NombreServicio'];
        $Descripcion = $_POST['Descripcion'];

        $Precio = $_POST['Precio'];
        $IDcategoria = $_POST['Categoria'];



        $stmt = $pdo->prepare("INSERT INTO Servicio (Fecha_servicio, Descripcion, Precio, Nombre_Servicio, IDcategoria, IDusuario, Ubicacion) VALUES (NOW(), ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$Descripcion, $Precio, $Nombre, $IDcategoria, $_SESSION['usuario'], "No especificada"]);

        if ($result) {
            header("Location: " . '/perfil/perfil.php');
            echo json_encode(["success" => true]);
            exit();
        } else {
            echo "Error: No se pudo crear el servicio.";
        }
    }

    if (isset($_POST['ServiciosEliminar'])) {
        $id = $_POST['id'] ?? null;

        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM Servicio WHERE IDservicio = ?");
            $stmt->execute([$id]);
            header("Location: " . '/perfil/perfil.php');
        } else {
            echo "ID no válido";
        }
    }

    if (isset($_POST['ServiciosEliminarAdmin'])) {
        $id = $_POST['id'] ?? null;

        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM Servicio WHERE IDservicio = ?");
            $stmt->execute([$id]);
        } else {
        }
    }

    if (isset($_POST['ServiciosEditar'])) {


        $id = intval($_POST['id']);
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $precio = floatval($_POST['precio']);

        $stmt = $pdo->prepare("UPDATE Servicio SET Nombre_Servicio = ?, Descripcion = ?, Precio = ? WHERE IDservicio = ? AND IDusuario = ?");
        $stmt->execute([$nombre, $descripcion, $precio, $id, $_SESSION['usuario']]);

        header("Location: " . '/perfil/perfil.php');
        exit();
    }

    if (isset($_POST['ServiciosEditarAdmin'])) {


        $id = intval($_POST['id']);
        $nombre = $_POST['Nombre'];
        $descripcion = $_POST['Descripcion'];
        $precio = intval($_POST['Precio']);
        $categoria = intval($_POST['Categoria']);
        $IDusr = intval($_POST['IDusr']);

        $stmt = $pdo->prepare("UPDATE Servicio SET Nombre_Servicio = ?, Descripcion = ?, Precio = ?, IDcategoria = ? WHERE IDservicio = ? AND IDusuario = ?");
        $stmt->execute([$nombre, $descripcion, $precio, $categoria, $id, $IDusr]);

        echo json_encode(['success' => true]);
        exit();
    }

    if (isset($_POST['ServiciosComprar'])) {

        $idservicio = $_POST['IDservicio'] ?? null;
        $idusuario = $_SESSION['usuario'] ?? null;

        $srv = new Servicios($pdo);
        $usr = new Usuario($pdo);
        $Usuario = $usr->buscarPorID($idusuario);
        $Rsrv = $srv->buscarPorID($idservicio);

        if ($idservicio && $idusuario) {
            $stmt = $pdo->prepare("INSERT INTO HistorialCompra (IDusuario, IDservicio) VALUES (?, ?)");
            $stmt->execute([$idusuario, $idservicio]);

            $stmt = $pdo->prepare("INSERT INTO Notificaciones (IDcliente,IDvendedor, Tipo, Mensaje) VALUES (?,?, 'Compra', ?)");
            $stmt->execute([$idusuario, $Rsrv['IDusuario'], '¡¡' . $Usuario['NombreUsuario'] . ' ha contratado tus servicios!!']);

            header("Location: " . '/pages/proveedores.php');
            exit();
        } else {
            echo "ID de servicio o usuario no válido";
        }
    }

    if (isset($_POST['ServiciosNotificacionesLeidas'])) {
        $idusuario = $_SESSION['usuario'] ?? null;
        $id = $_POST['id'] ?? null;
        if ($idusuario) {
            $stmt = $pdo->prepare("UPDATE Notificaciones SET Leido = 1 WHERE IDvendedor = ? AND Leido = 0 and ID = ?");
            $stmt->execute([$idusuario, $id]);

            echo json_encode(['success' => true]);
            exit();
        } else {
            echo "ID de usuario no válido";
        }
    }
}

if ($ServiciosMostrar === true) {
    require_once __DIR__ . '/../Conexion.php';
    $id = $_POST['id'] ?? $_SESSION['usuario'];
    $stmt = $pdo->prepare("SELECT IDservicio, Nombre_Servicio, Ubicacion, Precio, Descripcion FROM Servicio WHERE IDusuario = ?");
    $stmt->execute([$id]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
?>
        <div class="servicio-card" data-id="<?= $row['IDservicio'] ?>">
            <img src="https://picsum.photos/250/160?random=2" alt="<?= htmlspecialchars($row['Nombre_Servicio']) ?>">
            <div class="servicio-info">
                <h3><?= htmlspecialchars($row['Nombre_Servicio']) ?></h3>
                <p><?= htmlspecialchars($row['Descripcion']) ?></p>
                <p class="precio">Precio: <span>$<?= $row['Precio'] ?></span></p>
            </div>
            <div class="servicio-botones">
                <!-- <button>Ver</button>-->
                <button class="btn-editar" data-id="<?= $row['IDservicio'] ?>" data-modal="modal-editar-servicio">Editar</button>
                <button class="btn-eliminar" data-id="<?= $row['IDservicio'] ?>" type="button">Eliminar</button>
            </div>
        </div>
    <?php

    }
}

if ($ServiciosMostrar === "Proveedores") {
    require_once __DIR__ . '/../Conexion.php';
    require_once __DIR__ . "/consultas-Servicios.php";
    require_once __DIR__ . "/../Usuario/consultas-usuario.php";

    $srv = new Servicios($pdo);
    $usr = new Usuario($pdo);
    $Rsrv = $srv->Buscar();

    while ($servicio = $Rsrv->fetch(PDO::FETCH_ASSOC)) {
        $Rcat = $srv->buscarCategoria($servicio['IDcategoria']);
        $Rusr = $usr->buscarPorID($servicio['IDusuario']);
    ?>
        <form action="/PHP/Servicios/Servicios.php" method="post">
            <div class="card"
                data-nombre="<?= htmlspecialchars($servicio['Nombre_Servicio']) ?>"
                data-servicio="<?= htmlspecialchars($Rcat['IDcategoria']) ?>"
                data-ubicacion="Montevideo"
                data-precio="<?= htmlspecialchars($servicio['Precio']) ?>"
                data-etiquetas="eléctrico">
                <img src="../assets/img/sillon.png" alt="">
                <h4><?= htmlspecialchars($servicio['Nombre_Servicio']) ?></h4>
                <div class="persona"><?= htmlspecialchars($Rusr['NombreUsuario']) ?></div>
                <div class="ubicacion">Montevideo</div>
                <div class="precio">$<?= htmlspecialchars($servicio['Precio']) ?></div>
                <a href="/perfil/perfil-publico.php?id=<?= $Rusr['IDusuario'] ?>" class="btn-ver-perfil">Ver Perfil</a>

                <input type="hidden" name="IDservicio" value="<?= htmlspecialchars($servicio['IDservicio']) ?>">
                <?php
                if (isset($_SESSION['usuario']) && $_SESSION['usuario'] === $servicio['IDusuario']) {
                    // El usuario es el dueño del servicio, no mostrar el botón de comprar
                    continue;
                } ?>
                <button class="btn-ver-perfil" name="ServiciosComprar" type="submit">Comprar</button>

            </div>
        </form>
    <?php
    }
}

if ($ServiciosMostrar === "PanelAdmin") {
    require_once __DIR__ . '/../Conexion.php';
    require_once __DIR__ . "/consultas-Servicios.php";
    require_once __DIR__ . "/../Usuario/consultas-usuario.php";
    require_once __DIR__ . "/consultas-Servicios.php";
    $srv = new Servicios($pdo);
    $usr = new Usuario($pdo);
    $Rsrv = $srv->Buscar();

    while ($servicio = $Rsrv->fetch(PDO::FETCH_ASSOC)) {
        $Rcat = $srv->buscarCategoria($servicio['IDcategoria']);
        $Rusr = $usr->buscarPorID($servicio['IDusuario']);
        $categorias = $srv->Categorias();
    ?>

        <div
            class="buscador-admin divServicio"
            data-idserv="<?= $servicio['IDservicio'] ?>"
            data-nombreserv="<?= $servicio['Nombre_Servicio'] ?>">
            <div Class="ajustes-contenido" class="servicio-item">
                <p><strong>Servicio:</strong> <input type="text" name="NombreServ" value="<?= htmlspecialchars($servicio['Nombre_Servicio']) ?>"></p>
                <p><strong>Descripcion:</strong> <textarea name="descripcion" id="editar-descripcion"><?= htmlspecialchars($servicio['Descripcion']) ?></textarea></p>
                <p><strong>Precio:</strong> <input type="text" name="PrecioServ" value="<?= htmlspecialchars($servicio['Precio']) ?>"></p>
                <p><strong>Categoria:</strong> <select name="Categoria" required>
                        <?php
                        // Obtener todas las categorías
                        foreach ($categorias as $cat) {
                            $selected = ($cat['IDcategoria'] == $Rcat['IDcategoria']) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($cat['IDcategoria']) . '" ' . $selected . '>' . htmlspecialchars($cat['Nombre_Categoria']) . '</option>';
                        }
                        ?>
                    </select></p>
                <input type="hidden" name="IDusuario" value="<?= htmlspecialchars($servicio['IDusuario']) ?>">
                <p><strong>Vendedor:</strong> <?= htmlspecialchars($Rusr['NombreUsuario']) ?></p>
                <button data-idserv="<?= $servicio['IDservicio'] ?>" class="btn-accion updServicio">Editar</button>
                <button data-idserv="<?= $servicio['IDservicio'] ?>" class="btn-peligro delServicio">x</button>
            </div>
        </div>
    <?php
    }
}

if ($ServiciosMostrar === "MostrarNoticificaciones") {
    require_once __DIR__ . '/../Conexion.php';
    require_once __DIR__ . "/consultas-Servicios.php";

    $srv = new Servicios($pdo);

    $Rsrv = $srv->Notificacion($_SESSION['usuario']);

    while ($Notificacion = $Rsrv->fetch(PDO::FETCH_ASSOC)) {
        if ($Notificacion['Leido']) {
            continue; // Saltar notificaciones ya leídas
        }
    ?>
        <div data-idnoti="<?= $Notificacion['ID'] ?>" class="notificacion">
            <p><?= htmlspecialchars($Notificacion['Mensaje']) ?></p>
            <form action="/chat/chat.php" method="get" style="display:inline;">
                <input type="hidden" name="contacto" value="<?= htmlspecialchars($Notificacion['IDcliente']) ?>">
                <button data-idnoti="<?= $Notificacion['ID'] ?>" class="btn-comunicar notificacion" type="submit">Contactar</button>
            </form>
        </div>

<?php
    }
}
