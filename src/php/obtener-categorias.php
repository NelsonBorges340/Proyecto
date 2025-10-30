<?php
include 'db.php';
header('Content-Type: application/json');

$sql = "SELECT idCategoria as id, nombre FROM Categoria ORDER BY nombre";
$res = $conn->query($sql);
$categorias = [];

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $categorias[] = $row;
    }
}

echo json_encode($categorias);
?>