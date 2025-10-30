<?php
session_start();
require_once __DIR__ . '/../php/db.php';

function db(){ global $conn,$mysqli; return isset($conn)&&$conn instanceof mysqli ? $conn : (isset($mysqli)?$mysqli:null); }
$DB = db();
if (!$DB) { die("Error: no hay conexión a la base de datos."); }

function resolveUserId(mysqli $DB){
  foreach (['idUsuario','user_id','id','uid','usuario_id'] as $k) {
    if (!empty($_SESSION[$k]) && ctype_digit((string)$_SESSION[$k])) return (int)$_SESSION[$k];
  }
  if (!empty($_SESSION['email'])) {
    if ($stmt = $DB->prepare("SELECT idUsuario FROM Usuario WHERE email=? LIMIT 1")) {
      $stmt->bind_param("s", $_SESSION['email']);
      $stmt->execute(); $res = $stmt->get_result();
      if ($res && $res->num_rows) { $row=$res->fetch_assoc(); $stmt->close(); return (int)$row['idUsuario']; }
      $stmt->close();
    }
  }
  if (!empty($_SESSION['usuario']) && is_array($_SESSION['usuario'])) {
    if (!empty($_SESSION['usuario']['idUsuario'])) return (int)$_SESSION['usuario']['idUsuario'];
    if (!empty($_SESSION['usuario']['email'])) {
      if ($stmt = $DB->prepare("SELECT idUsuario FROM Usuario WHERE email=? LIMIT 1")) {
        $stmt->bind_param("s", $_SESSION['usuario']['email']);
        $stmt->execute(); $res = $stmt->get_result();
        if ($res && $res->num_rows) { $row=$res->fetch_assoc(); $stmt->close(); return (int)$row['idUsuario']; }
        $stmt->close();
      }
    }
  }
  return 0;
}

$idSesion = resolveUserId($DB);
$canEdit = $idSesion > 0;

// Selección de usuario a editar
if ($canEdit) {
  $idUsuario = $idSesion;
} else if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
  $idUsuario = (int)$_GET['id']; // vista sin sesión
} else {
  $q = $DB->query("SELECT idUsuario FROM Usuario ORDER BY idUsuario ASC LIMIT 1");
  if ($q && ($row = $q->fetch_assoc())) $idUsuario = (int)$row['idUsuario'];
  $q && $q->close();
}

// --- datos del usuario ---
$stmt = $DB->prepare("SELECT idUsuario, nombreCompleto, email FROM Usuario WHERE idUsuario=?");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

// --- imagen actual ---
$imgRow = null;
$stmt = $DB->prepare("SELECT imagen, tipo_mime FROM ImagenPerfil WHERE idUsuario=? ORDER BY idImagen DESC LIMIT 1");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows) { $imgRow = $res->fetch_assoc(); }
$stmt->close();

$mensaje = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!$canEdit || $idUsuario !== $idSesion) { http_response_code(403); die("Necesitás iniciar sesión para editar tu perfil."); }

  $nombre = trim($_POST['nombreCompleto'] ?? ($usuario['nombreCompleto'] ?? ''));

  // actualizar nombre
  $stmt = $DB->prepare("UPDATE Usuario SET nombreCompleto=? WHERE idUsuario=?");
  $stmt->bind_param("si", $nombre, $idUsuario);
  $stmt->execute();
  $stmt->close();
  $usuario['nombreCompleto'] = $nombre;

  // subir imagen si viene archivo
  if (!empty($_FILES['imagen_perfil']['name']) && $_FILES['imagen_perfil']['error'] === UPLOAD_ERR_OK) {
    $tmp  = $_FILES['imagen_perfil']['tmp_name'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp) ?: '';
    $allow = ['image/jpeg','image/png','image/gif','image/webp'];
    if (!in_array($mime, $allow)) {
      $mensaje = "Formato no permitido. Usa JPG, PNG, GIF o WEBP.";
    } else {
      $data = file_get_contents($tmp);
      $DB->query("DELETE FROM ImagenPerfil WHERE idUsuario={$idUsuario}");
      $ins = $DB->prepare("INSERT INTO ImagenPerfil (idUsuario, imagen, tipo_mime) VALUES (?,?,?)");
      $null = NULL;
      $ins->bind_param("ibs", $idUsuario, $null, $mime);
      $ins->send_long_data(1, $data);
      $ok = $ins->execute();
      $ins->close();
      if ($ok) { $imgRow = ['imagen'=>$data,'tipo_mime'=>$mime]; $mensaje = "Perfil actualizado."; }
      else { $mensaje = "No se pudo guardar la imagen."; }
    }
  } else {
    if (!$mensaje) $mensaje = "Perfil actualizado (sin cambiar la imagen).";
  }
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$src = '../img/Diseño sin título (10).png';
if ($imgRow && !empty($imgRow['imagen'])) {
  $src = 'data:' . ($imgRow['tipo_mime'] ?? 'image/jpeg') . ';base64,' . base64_encode($imgRow['imagen']);
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Editar perfil</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root {
  /* 🎨 Paleta MANEKI */
  --bg: #f5f3ef;          /* fondo general beige claro */
  --card: #ffffff;        /* fondo de tarjetas o formularios */
  --line: #d9c8b6;        /* bordes suaves beige */
  --text: #2f3c33;        /* texto principal gris verdoso oscuro */
  --brand: #6e9277;       /* verde principal MANEKI */
  --brand-hover: #527460; /* verde oscuro al pasar el mouse */
  --shadow: 0 10px 20px rgba(47, 60, 51, 0.08), 0 4px 8px rgba(47, 60, 51, 0.04);
  --radius: 12px;
}

body {
  font-family: system-ui, Segoe UI, Arial, sans-serif;
  background: var(--bg);
  margin: 0;
  color: var(--text);
}

/* 🔝 Barra de navegación */
nav {
  background: var(--brand);
  color: #fff;
  padding: 14px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: var(--shadow);
}

nav a {
  color: #fff;
  text-decoration: none;
  margin-left: 20px;
  font-weight: 500;
}

nav a:hover {
  text-decoration: underline;
}

/* 📦 Contenedor principal */
main {
  max-width: 900px;
  margin: 24px auto;
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 20px;
}

/* 🧾 Titulares y estructura */
h1 {
  margin: 0 0 16px;
  color: var(--text);
}

.row {
  display: flex;
  gap: 24px;
  align-items: flex-start;
  flex-wrap: wrap;
}

/* 🖼️ Imagen de vista previa */
.preview {
  width: 180px;
  aspect-ratio: 1;
  border-radius: 12px;
  border: 1px solid var(--line);
  object-fit: cover;
}

/* 📋 Formulario */
form .group {
  margin-bottom: 12px;
}

label {
  display: block;
  margin-bottom: 6px;
  color: #3e4a42;
}

input[type="text"],
input[type="email"] {
  width: 100%;
  padding: 10px;
  border: 1px solid var(--line);
  border-radius: 8px;
  background: #fff;
  color: var(--text);
}

input[type="file"] {
  display: block;
  margin-top: 6px;
}

/* 🔘 Botones */
button {
  background: var(--brand);
  color: #fff;
  border: none;
  padding: 10px 14px;
  border-radius: 10px;
  cursor: pointer;
  transition: background 0.2s ease;
}

button:hover {
  background: var(--brand-hover);
}

button[disabled] {
  opacity: 0.6;
  cursor: not-allowed;
}

/* 🔔 Alertas */
.alert {
  background: #e1d0bd;
  border: 1px solid var(--line);
  color: var(--text);
  border-radius: 8px;
  padding: 8px 10px;
  margin-bottom: 12px;
}

.warn {
  background: #fff7ed;
  border: 1px solid #fed7aa;
  color: #9a3412;
}

</style>
</head>
<body>
<nav>
  <div><strong>Mi Cuenta</strong></div>
  <div>
    <a href="index.php">Inicio</a>
    <a href="perfil.php">Volver al Perfil</a>
  </div>
</nav>

<main>
  <h1>Editar perfil</h1>
  <?php if(isset($mensaje)): ?><div class="alert"><?=h($mensaje)?></div><?php endif; ?>
  <?php if(!$canEdit): ?><div class="alert warn">Estás viendo esta página sin sesión. El formulario está deshabilitado.</div><?php endif; ?>

  <div class="row">
    <img class="preview" id="preview" src="<?=h($src)?>" alt="Foto de perfil">
    <form method="post" enctype="multipart/form-data">
      <div class="group">
        <label>Nombre completo</label>
        <input type="text" name="nombreCompleto" value="<?=h($usuario['nombreCompleto'] ?? '')?>" <?= $canEdit ? '' : 'disabled' ?> required>
      </div>
      <div class="group">
        <label>Foto de perfil</label>
        <input type="file" name="imagen_perfil" accept=".jpg,.jpeg,.png,.gif,.webp" onchange="previewFile(this)" <?= $canEdit ? '' : 'disabled' ?>>
      </div>
      <button type="submit" <?= $canEdit ? '' : 'disabled' ?>>Guardar</button>
    </form>
  </div>
</main>
<script>
function previewFile(inp){
  if (!inp.files || !inp.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => { document.getElementById('preview').src = e.target.result; };
  reader.readAsDataURL(inp.files[0]);
}
</script>
</body>
</html>
