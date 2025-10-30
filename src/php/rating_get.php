<?php
// C:\xampp\htdocs\proyecto_its\php\rating_get.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

function error_json($m,$c=400){ http_response_code($c); echo json_encode(['ok'=>false,'error'=>$m]); exit; }

$servicio_id = isset($_GET['servicio_id']) ? (int)$_GET['servicio_id'] : 0;
if ($servicio_id<=0) error_json('servicio_id inválido');

// Promedio + cantidad
$q = $conn->prepare("SELECT AVG(calificacion) AS promedio, COUNT(*) AS cantidad FROM Resena WHERE idServicio=?");
$q->bind_param("i", $servicio_id);
$q->execute();
$agg = $q->get_result()->fetch_assoc();
$q->close();
$promedio = $agg && $agg['promedio']!==null ? round((float)$agg['promedio'],2) : 0.0;
$cantidad = $agg ? (int)$agg['cantidad'] : 0;

// Distribución
$dist = [1=>0,2=>0,3=>0,4=>0,5=>0];
$d = $conn->prepare("SELECT calificacion, COUNT(*) c FROM Resena WHERE idServicio=? GROUP BY 1");
$d->bind_param("i",$servicio_id);
$d->execute();
$res = $d->get_result();
while($row = $res->fetch_assoc()){
  $p = (int)$row['calificacion']; if ($p>=1 && $p<=5) $dist[$p] = (int)$row['c'];
}
$d->close();

// Últimas reseñas (cliente -> usuario para nombre)
$rev = $conn->prepare("
  SELECT r.calificacion, r.comentario, r.fechaResena, u.nombreCompleto
  FROM Resena r
  JOIN Cliente c ON c.idCliente = r.idCliente
  JOIN Usuario u ON u.idUsuario = c.idCliente
  WHERE r.idServicio=?
  ORDER BY r.fechaResena DESC, r.idResena DESC
  LIMIT 10");
$rev->bind_param("i",$servicio_id);
$rev->execute();
$reviews = [];
$rr = $rev->get_result();
while($row = $rr->fetch_assoc()){
  $reviews[] = [
    'puntuacion'=>(int)$row['calificacion'],
    'comentario'=>$row['comentario'],
    'fecha'=>$row['fechaResena'],
    'cliente'=>$row['nombreCompleto']
  ];
}
$rev->close();

echo json_encode([
  'ok'=>true,
  'promedio'=>$promedio,
  'cantidad'=>$cantidad,
  'distribucion'=>[ '1'=>$dist[1],'2'=>$dist[2],'3'=>$dist[3],'4'=>$dist[4],'5'=>$dist[5] ],
  'reviews'=>$reviews
]);
