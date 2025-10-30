<?php
include 'db.php';

header('Content-Type: application/json');

if ($_GET['tipo'] === 'usuarios') {
    $usuarios = $conn->query("SELECT idUsuario AS id, nombreCompleto AS usuario, email AS correo, rol FROM Usuario");
    $result = [];
    while ($row = $usuarios->fetch_assoc()) {
        $result[] = $row;
    }
    echo json_encode($result);
}  elseif ($_GET['tipo'] === 'servicios') {
    $sql = "SELECT 
                s.idServicio AS id,
                s.titulo,
                COALESCE(c.nombre, 'Sin categoría') AS categoria,
                s.precio
            FROM Servicio s
            LEFT JOIN Categoria c ON s.idCategoria = c.idCategoria
            ORDER BY s.idServicio DESC";
    $servicios = $conn->query($sql);

    $result = [];
    while ($row = $servicios->fetch_assoc()) {
        $result[] = $row;
    }
    echo json_encode($result);
}
 elseif ($_GET['tipo'] === 'categorias') {
    $sql = "SELECT 
                c.idCategoria,
                c.nombre,
                c.descripcion,
                (SELECT COUNT(*) FROM Servicio s WHERE s.idCategoria = c.idCategoria) AS total_servicios
            FROM Categoria c
            ORDER BY c.nombre";
    $res = $conn->query($sql);
    $categorias = [];
    while ($row = $res->fetch_assoc()) {
        $categorias[] = $row;
    }
    echo json_encode($categorias);
}
?>