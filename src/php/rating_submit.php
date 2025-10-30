<?php
// C:\xampp\htdocs\proyecto_its\php\rating_submit.php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/db.php';

function error_json($m,$c=400){ http_response_code($c); echo json_encode(['ok'=>false,'error'=>$m]); exit; }

// Auth con llaves de sesión del proyecto
if (!isset($_SESSION['usuario_id'])) error_json('No autenticado', 401);
$usuario_id = (int)$_SESSION['usuario_id'];
$rol = $_SESSION['usuario_rol'] ?? '';

if ($rol !== 'cliente') error_json('Solo clientes pueden calificar', 403);

// Mapear a idCliente (en este esquema, idCliente == idUsuario para rol cliente)
$idCliente = $usuario_id;

// Input
$servicio_id = isset($_POST['servicio_id']) ? (int)$_POST['servicio_id'] : 0;
$puntuacion  = isset($_POST['puntuacion'])  ? (int)$_POST['puntuacion']  : 0;
$comentario  = isset($_POST['comentario'])  ? trim((string)$_POST['comentario']) : null;

if ($servicio_id<=0) error_json('servicio_id inválido');
if ($puntuacion<1 || $puntuacion>5) error_json('puntuacion debe ser 1..5');

$hoy = date('Y-m-d');

// Upsert manual (la tabla Resena no tiene UNIQUE(idCliente,idServicio))
$sel = $conn->prepare("SELECT idResena FROM Resena WHERE idCliente=? AND idServicio=? LIMIT 1");
$sel->bind_param("ii", $idCliente, $servicio_id);
$sel->execute();
$existe = $sel->get_result()->fetch_assoc();
$sel->close();

if ($existe) {
  $upd = $conn->prepare("UPDATE Resena SET calificacion=?, comentario=?, fechaResena=? WHERE idResena=?");
  $upd->bind_param("issi", $puntuacion, $comentario, $hoy, $existe['idResena']);
  if (!$upd->execute()) error_json('DB update: '.$upd->error, 500);
  $upd->close();
} else {
  $ins = $conn->prepare("INSERT INTO Resena (idCliente,idServicio,comentario,calificacion,fechaResena) VALUES (?,?,?,?,?)");
  $ins->bind_param("iisis", $idCliente, $servicio_id, $comentario, $puntuacion, $hoy);
  if (!$ins->execute()) error_json('DB insert: '.$ins->error, 500);
  $ins->close();
}

// Devolver nuevo promedio/cantidad
$q = $conn->prepare("SELECT AVG(calificacion) AS promedio, COUNT(*) AS cantidad FROM Resena WHERE idServicio=?");
$q->bind_param("i", $servicio_id);
$q->execute();
$agg = $q->get_result()->fetch_assoc();
$q->close();

echo json_encode([
  'ok'=>true,
  'promedio'=> round((float)($agg['promedio'] ?? 0), 2),
  'cantidad'=> (int)($agg['cantidad'] ?? 0)
]);
