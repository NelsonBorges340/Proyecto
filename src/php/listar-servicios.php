<?php
// C:\xampp\htdocs\proyecto_its\php\listar-servicios.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$DB = isset($mysqli) && $mysqli instanceof mysqli ? $mysqli : (isset($conn) ? $conn : null);
if (!$DB) { echo json_encode([]); exit; }

$sql = "SELECT s.idServicio    AS idServicio,
               s.titulo        AS titulo,
               s.descripcion   AS descripcion,
               s.precio        AS precio,
               s.ubicacion     AS ubicacion,
               c.nombre        AS categoria
        FROM Servicio s
        LEFT JOIN Categoria c ON c.idCategoria = s.idCategoria
        ORDER BY s.idServicio DESC";
$rs = $DB->query($sql);

$out = [];
while ($r = $rs->fetch_assoc()) {
  // No enviamos base64 para no inflar JSON; el HTML ya usa mostrar-imagen.php si 'imagen' es falsy
  $out[] = [
    'idServicio' => (int)$r['idServicio'],
    'titulo'     => $r['titulo'],
    'descripcion'=> $r['descripcion'],
    'precio'     => (float)$r['precio'],
    'ubicacion'  => $r['ubicacion'],
    'categoria'  => $r['categoria'],
    'imagen'     => null // o podés enviar "../php/mostrar-imagen.php?id=".$r['idServicio']
  ];
}
echo json_encode($out, JSON_UNESCAPED_UNICODE);
