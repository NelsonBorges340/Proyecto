<?php
session_start();
require_once __DIR__ . '/../php/db.php';

function db(){ global $conn,$mysqli; return isset($conn)&&$conn instanceof mysqli ? $conn : (isset($mysqli)?$mysqli:null); }
$DB = db();
if (!$DB) { die("Error: no hay conexión a la base de datos."); }

/** Resuelve el id del usuario desde distintas claves de sesión o por email */
function resolveUserId(mysqli $DB){
  // 1) Claves comunes de ID
  foreach (['idUsuario','user_id','id','uid','usuario_id'] as $k) {
    if (!empty($_SESSION[$k]) && ctype_digit((string)$_SESSION[$k])) return (int)$_SESSION[$k];
  }
  // 2) Si hay email en sesión, obtener idUsuario
  if (!empty($_SESSION['email'])) {
    if ($stmt = $DB->prepare("SELECT idUsuario FROM Usuario WHERE email=? LIMIT 1")) {
      $stmt->bind_param("s", $_SESSION['email']);
      $stmt->execute();
      $res = $stmt->get_result();
      if ($res && $res->num_rows) { $row=$res->fetch_assoc(); $stmt->close(); return (int)$row['idUsuario']; }
      $stmt->close();
    }
  }
  // 3) Si guardaste el usuario entero en la sesión
  if (!empty($_SESSION['usuario']) && is_array($_SESSION['usuario'])) {
    if (!empty($_SESSION['usuario']['idUsuario'])) return (int)$_SESSION['usuario']['idUsuario'];
    if (!empty($_SESSION['usuario']['email'])) {
      if ($stmt = $DB->prepare("SELECT idUsuario FROM Usuario WHERE email=? LIMIT 1")) {
        $stmt->bind_param("s", $_SESSION['usuario']['email']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows) { $row=$res->fetch_assoc(); $stmt->close(); return (int)$row['idUsuario']; }
        $stmt->close();
      }
    }
  }
  return 0; // no se detectó sesión
}

$notice = null;
$idUsuario = resolveUserId($DB);

if ($idUsuario > 0) {
  // Sesión válida: OK
} elseif (isset($_GET['id']) && ctype_digit($_GET['id'])) {
  $idUsuario = (int)$_GET['id'];
  $notice = "Vista del usuario #{$idUsuario}. (No se detectó sesión)";
} else {
  // Fallback solo para ver la página
  $q = $DB->query("SELECT idUsuario FROM Usuario ORDER BY idUsuario ASC LIMIT 1");
  if ($q && ($row = $q->fetch_assoc())) {
    $idUsuario = (int)$row['idUsuario'];
    $notice = "Mostrando el primer usuario de la base (no se detectó sesión).";
  } else {
    die("No hay usuarios en la base de datos.");
  }
  $q && $q->close();
}

// --- datos del usuario ---
$stmt = $DB->prepare("SELECT idUsuario, nombreCompleto, email, rol FROM Usuario WHERE idUsuario=?");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$res = $stmt->get_result();
$usuario = $res && $res->num_rows ? $res->fetch_assoc() : null;
$stmt->close();
if (!$usuario) { die("Usuario no encontrado."); }

// --- imagen (ImagenPerfil) ---
$imgRow = null;
$stmt = $DB->prepare("SELECT imagen, tipo_mime FROM ImagenPerfil WHERE idUsuario=? ORDER BY idImagen DESC LIMIT 1");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$r = $stmt->get_result();
if ($r && $r->num_rows) { $imgRow = $r->fetch_assoc(); }
$stmt->close();

$src = '../img/Diseño sin título (10).png';
if ($imgRow && !empty($imgRow['imagen'])) {
  $src = 'data:' . ($imgRow['tipo_mime'] ?? 'image/jpeg') . ';base64,' . base64_encode($imgRow['imagen']);
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$haySesion = $idUsuario > 0 && empty($notice);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Perfil</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root {
  /* 🎨 Paleta MANEKI */
  --bg: #f5f3ef;          /* fondo general beige claro */
  --card: #ffffff;        /* fondo de las tarjetas */
  --text: #2f3c33;        /* texto principal */
  --muted: #6e9277;       /* verde tenue para texto secundario */
  --line: #d9c8b6;        /* líneas y bordes */
  --brand: #6e9277;       /* verde principal */
  --brand-hover: #527460; /* verde más oscuro para hover */
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
  color: var(--card);
  padding: 14px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: var(--shadow);
}

nav a {
  color: var(--card);
  text-decoration: none;
  margin-left: 20px;
  font-weight: 500;
}

nav a:hover {
  text-decoration: underline;
}

/* 📦 Contenido principal */
main {
  max-width: 900px;
  margin: 40px auto;
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 32px;
}

/* 🧾 Títulos y texto */
h1 {
  font-size: 1.8rem;
  margin-top: 0;
}

img.perfil {
  width: 180px;
  height: 180px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--line);
  margin-bottom: 20px;
}

.info label {
  display: block;
  font-weight: 600;
  margin-top: 12px;
}

.info p {
  margin: 4px 0 12px;
  color: var(--muted);
}

/* 🔘 Botones */
a.btn {
  display: inline-block;
  margin-top: 20px;
  padding: 10px 18px;
  background: var(--brand);
  color: #fff;
  text-decoration: none;
  border-radius: 10px;
  font-weight: 500;
  transition: background 0.2s ease;
}

a.btn:hover {
  background: var(--brand-hover);
}

/* 🔔 Alertas o avisos */
.alert {
  background: #e1d0bd;
  border: 1px solid var(--line);
  color: var(--text);
  border-radius: 10px;
  padding: 10px 14px;
  margin: 16px 0;
}

</style>
</head>
<body>
<nav>
  <div><strong>Mi Cuenta</strong></div>
  <div>
    <a href="index.php">Inicio</a>
    <a href="editar-perfil.php">Editar Perfil</a>
  </div>
</nav>

<main>
  <?php if($notice): ?><div class="alert"><?=h($notice)?></div><?php endif; ?>
  <h1>Perfil de <?=h($usuario['nombreCompleto'])?></h1>
  <img class="perfil" src="<?=h($src)?>" alt="Foto de perfil">

  <div class="info">
    <label>Email:</label>
    <p><?=h($usuario['email'])?></p>

    <label>Rol:</label>
    <p><?=h($usuario['rol'])?></p>

    <a class="btn" href="editar-perfil.php">Editar Perfil</a>
  </div>
</main>
</body>
</html>
