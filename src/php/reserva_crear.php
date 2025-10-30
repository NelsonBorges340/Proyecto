<?php
// php/reserva_crear.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
session_start();
require __DIR__ . '/db.php';

function db() {
  global $conn, $mysqli;
  return (isset($mysqli) && $mysqli instanceof mysqli) ? $mysqli
       : ((isset($conn) && $conn instanceof mysqli) ? $conn : null);
}
function error_json($m,$c=400){ http_response_code($c); echo json_encode(['ok'=>false,'error'=>$m]); exit; }

$DB = db();
if (!$DB) error_json('DB', 500);

$servicio_id = isset($_POST['servicio_id']) ? (int)$_POST['servicio_id'] : 0;
$fecha       = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';

if ($servicio_id <= 0) error_json('servicio_id inválido');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) error_json('fecha inválida (YYYY-MM-DD)');

// Cliente desde la sesión (ajusta a tu login)
$idCliente = (int)($_SESSION['usuario_id'] ?? 1); // fallback demo a 1

// Validar fecha
$d = DateTime::createFromFormat('Y-m-d', $fecha);
if (!$d || $d->format('Y-m-d') !== $fecha) error_json('fecha inválida');
$tomorrow = new DateTime('tomorrow');
if ($d < $tomorrow) error_json('No se puede reservar para hoy o pasado');

// Confirmar que el servicio existe (y leer info si querés)
$st = $DB->prepare("SELECT idServicio FROM Servicio WHERE idServicio=?");
$st->bind_param('i', $servicio_id);
$st->execute();
$svc = $st->get_result()->fetch_assoc();
$st->close();
if (!$svc) error_json('Servicio no encontrado', 404);

// Evitar doble reserva (confirmada) para ese día
$st = $DB->prepare("SELECT 1 FROM Reserva WHERE idServicio=? AND fechaReserva=? AND estado='confirmada' LIMIT 1");
$st->bind_param('is', $servicio_id, $fecha);
$st->execute();
$exists = (bool)$st->get_result()->fetch_row();
$st->close();
if ($exists) error_json('Ese día ya está reservado', 409);

// Insertar como confirmada
$st = $DB->prepare("INSERT INTO Reserva (idCliente, idServicio, fechaReserva, estado) VALUES (?, ?, ?, 'confirmada')");
$st->bind_param('iis', $idCliente, $servicio_id, $fecha);
$ok = $st->execute();
if (!$ok) error_json('Error DB: '.$DB->error, 500);
$idReserva = (int)$st->insert_id;
$st->close();

// URL factura
$facturaUrl = sprintf('../html/factura.php?id=%d', $idReserva);
echo json_encode(['ok'=>true, 'idReserva'=>$idReserva, 'factura'=>$facturaUrl]);
