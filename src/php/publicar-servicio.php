<?php
// php/publicar-servicio.php  -> HANDLER de ALTA
session_start();
require_once __DIR__ . '/db.php';

// tomar conexión ($mysqli o $conn)
$DB = (isset($mysqli) && $mysqli instanceof mysqli) ? $mysqli : ((isset($conn) && $conn instanceof mysqli) ? $conn : null);
if (!$DB) { die("Sin conexión a la base de datos."); }

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$DB->set_charset('utf8mb4');

// -------- sesión -> idProveedor --------
$idUsuario = 0;
foreach (['idUsuario','user_id','id','uid','usuario_id'] as $k) {
  if (!empty($_SESSION[$k]) && ctype_digit((string)$_SESSION[$k])) { $idUsuario = (int)$_SESSION[$k]; break; }
}
if ($idUsuario <= 0) {
  http_response_code(401);
  die("ERROR: no hay sesión válida.");
}

// asegurar fila en Proveedor (FK Servicio.idProveedor)
$stmt = $DB->prepare("SELECT 1 FROM Proveedor WHERE idProveedor=? LIMIT 1");
$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$existeProv = $stmt->get_result()->num_rows > 0;
$stmt->close();

if (!$existeProv) {
  $q = $DB->prepare("SELECT nombreCompleto FROM Usuario WHERE idUsuario=? LIMIT 1");
  $q->bind_param('i', $idUsuario);
  $q->execute();
  $u = $q->get_result()->fetch_assoc();
  $q->close();
  if (!$u) { die("ERROR: el usuario no existe."); }
  $nombreNegocio = $u['nombreCompleto'] ?: ('Proveedor '.$idUsuario);
  $insP = $DB->prepare("INSERT INTO Proveedor (idProveedor, nombreNegocio, descripcion, especialidad) VALUES (?,?,NULL,NULL)");
  $insP->bind_param('is', $idUsuario, $nombreNegocio);
  $insP->execute();
  $insP->close();
}

// -------- leer POST --------
$titulo      = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precioStr   = trim($_POST['precio'] ?? '');
$ubicacion   = trim($_POST['ubicacion'] ?? '');
$idCategoria = $_POST['categoria_id'] ?? null;

if ($titulo === '')    { http_response_code(400); die("ERROR: título obligatorio."); }
if ($precioStr === '') { http_response_code(400); die("ERROR: precio obligatorio."); }
$precio = str_replace(',', '.', $precioStr);
if (!is_numeric($precio)) { http_response_code(400); die("ERROR: precio inválido."); }
$precio = (float)$precio;

// categoria: vacío/0 -> NULL; si viene valor, validar que exista
if ($idCategoria === '' || $idCategoria === null || (string)$idCategoria === '0') {
  $idCategoria = null;
} else {
  $idCategoria = (int)$idCategoria;
  $c = $DB->prepare("SELECT 1 FROM Categoria WHERE idCategoria=? LIMIT 1");
  $c->bind_param('i', $idCategoria);
  $c->execute();
  $okCat = $c->get_result()->num_rows > 0;
  $c->close();
  if (!$okCat) { $idCategoria = null; }
}

$DB->begin_transaction();
try {
  // ---- insertar servicio ----
  if (is_null($idCategoria)) {
    $sql = "INSERT INTO Servicio (idProveedor, titulo, descripcion, precio, ubicacion, idCategoria)
            VALUES (?,?,?,?,?,NULL)";
    $stmt = $DB->prepare($sql);
    $stmt->bind_param('issds', $idUsuario, $titulo, $descripcion, $precio, $ubicacion);
  } else {
    $sql = "INSERT INTO Servicio (idProveedor, titulo, descripcion, precio, ubicacion, idCategoria)
            VALUES (?,?,?,?,?,?)";
    $stmt = $DB->prepare($sql);
    $stmt->bind_param('issdsi', $idUsuario, $titulo, $descripcion, $precio, $ubicacion, $idCategoria);
  }
  $stmt->execute();
  $idServicio = $stmt->insert_id;
  $stmt->close();

  // ----- IMAGEN opcional (solo archivo + ruta, sin BLOB) -----
  if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $tmp  = $_FILES['imagen']['tmp_name'];
    $name = $_FILES['imagen']['name'];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($tmp) ?: 'image/jpeg';
    $allowMime = ['image/jpeg','image/png','image/gif','image/webp'];
    $allowExt  = ['jpg','jpeg','png','gif','webp'];
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($mime, $allowMime) || !in_array($ext, $allowExt)) { throw new Exception("Formato de imagen no permitido."); }

    // carpeta ../img/servicios/{idServicio}
    $imgRoot = __DIR__ . '/../img';
    if (!is_dir($imgRoot)) { @mkdir($imgRoot, 0755, true); }
    $svcDir = __DIR__ . '/../img/servicios/' . $idServicio;
    if (!is_dir($svcDir)) { @mkdir($svcDir, 0755, true); }

    $fileName = 'svc_'.$idServicio.'_'.date('Ymd_His').'.'.$ext;
    $destAbs  = $svcDir . '/' . $fileName;
    $destRel  = 'img/servicios/'.$idServicio.'/'.$fileName;

    if (!move_uploaded_file($tmp, $destAbs)) { throw new Exception("No se pudo mover la imagen subida."); }

    // ¿ya hay principal?
    $q = $DB->prepare("SELECT 1 FROM ImagenServicio WHERE idServicio=? AND es_principal=1 LIMIT 1");
    $q->bind_param('i', $idServicio);
    $q->execute();
    $tienePrincipal = $q->get_result()->num_rows > 0;
    $q->close();
    $es_principal = $tienePrincipal ? 0 : 1;

    // Insert SOLO ruta + mime (sin BLOB)
    $ins = $DB->prepare("INSERT INTO ImagenServicio (idServicio, es_principal, tipo_mime, ruta_archivo)
                         VALUES (?,?,?,?)");
    $ins->bind_param('iiss', $idServicio, $es_principal, $mime, $destRel);
    $ins->execute();
    $ins->close();
  }

  $DB->commit();

  // ---- Confirmación simple (sin redirigir) ----
  ?>
  <!doctype html>
  <html lang="es"><head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Servicio creado</title>
  <style>
    body{font-family:system-ui,Segoe UI,Arial,sans-serif;background:#f5f7fb;margin:0}
    main{max-width:720px;margin:40px auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px}
    a.btn{display:inline-block;padding:10px 14px;border-radius:10px;border:1px solid #e5e7eb;background:#fff;margin-right:8px;text-decoration:none;color:#111}
    a.primary{background:#2563eb;border-color:#2563eb;color:#fff}
  </style></head><body>
  <main>
    <h1>✅ Servicio creado</h1>
    <p><strong>ID:</strong> <?= (int)$idServicio ?></p>
    <div style="margin-top:12px">
      <a class="btn primary" href="../html/ver-servicio.php?id=<?= (int)$idServicio ?>">Ver servicio</a>
      <a class="btn" href="../html/publicar-servicio.php">Crear otro</a>
      <a class="btn" href="../html/index.php">Ir al inicio</a>
    </div>
  </main>
  </body></html>
  <?php
  exit;

} catch (Throwable $e) {
  // Si la conexión ya se cayó, evita fatal en rollback
  try { $DB->rollback(); } catch (Throwable $e2) {}
  http_response_code(500);
  die("ERROR SQL al insertar servicio: ".$e->getMessage());
}
