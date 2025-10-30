<?php
// php/reserva_get_calendario.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/db.php';

function db() {
  global $conn, $mysqli;
  return (isset($mysqli) && $mysqli instanceof mysqli) ? $mysqli
       : ((isset($conn) && $conn instanceof mysqli) ? $conn : null);
}
function error_json($m,$c=400){ http_response_code($c); echo json_encode(['ok'=>false,'error'=>$m]); exit; }

$DB = db();
if (!$DB) error_json('DB');

$servicio_id = isset($_GET['servicio_id']) ? (int)$_GET['servicio_id'] : 0;
if ($servicio_id <= 0) error_json('servicio_id inválido');

$hoy   = new DateTime('today');
$min   = (clone $hoy)->modify('+1 day')->format('Y-m-d');   // desde mañana
$hasta = (clone $hoy)->modify('+60 day')->format('Y-m-d');  // ventana opcional

// Fechas ocupadas SOLO confirmadas
$sql = "SELECT fechaReserva 
        FROM Reserva 
        WHERE idServicio=? AND estado='confirmada' AND fechaReserva BETWEEN ? AND ?
        ORDER BY fechaReserva";
$st = $DB->prepare($sql);
$st->bind_param('iss', $servicio_id, $min, $hasta);
$st->execute();
$rs = $st->get_result();

$reservados = [];
while ($row = $rs->fetch_assoc()) $reservados[] = $row['fechaReserva'];
$st->close();

echo json_encode(['ok'=>true, 'min'=>$min, 'reservados'=>$reservados]);
