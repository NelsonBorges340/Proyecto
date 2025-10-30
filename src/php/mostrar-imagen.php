<?php
// C:\xampp\htdocs\proyecto_its\php\mostrar-imagen.php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

// Soporta $mysqli o $conn
$DB = null;
if (isset($mysqli) && $mysqli instanceof mysqli) $DB = $mysqli;
elseif (isset($conn) && $conn instanceof mysqli) $DB = $conn;
if (!$DB) { http_response_code(500); exit('DB'); }

$idServicio = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($idServicio <= 0) { http_response_code(400); exit('id'); }

// Traer la imagen principal o la última
$sql = "SELECT ruta_archivo, imagen, tipo_mime
        FROM ImagenServicio
        WHERE idServicio=?
        ORDER BY es_principal DESC, idImagen DESC
        LIMIT 1";
$st = $DB->prepare($sql);
$st->bind_param('i', $idServicio);
$st->execute();
$res = $st->get_result();
$row = $res->fetch_assoc();
$st->close();

// 1) Si hay ruta a archivo, lo servimos desde disco
if ($row && !empty($row['ruta_archivo'])) {
  // La ruta en DB suele ser p.ej: "img/uploads/servicios/4_abc.jpg"
  $rel = ltrim($row['ruta_archivo'], '/\\');
  $base = realpath(__DIR__ . '/..');                    // .../proyecto_its
  $full = realpath($base . DIRECTORY_SEPARATOR . $rel); // ruta absoluta normalizada

  // Seguridad: que quede dentro del proyecto
  if ($full && str_starts_with($full, $base) && is_file($full)) {
    $mime = function_exists('mime_content_type') ? mime_content_type($full) : 'image/jpeg';
    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=86400');
    readfile($full);
    exit;
  }
}

// 2) Si hay BLOB en DB, lo devolvemos
if ($row && !empty($row['imagen'])) {
  $mime = !empty($row['tipo_mime']) ? $row['tipo_mime'] : 'image/jpeg';
  header('Content-Type: ' . $mime);
  header('Cache-Control: public, max-age=86400');
  echo $row['imagen'];
  exit;
}

// 3) Fallback: placeholder
$ph = realpath(__DIR__ . '/../img/placeholder_servicio.png');
if ($ph && is_file($ph)) {
  header('Content-Type: image/png');
  header('Cache-Control: public, max-age=86400');
  readfile($ph);
  exit;
}

http_response_code(404);
echo 'no-img';
