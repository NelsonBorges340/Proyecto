<?php
include 'db.php';
header('Content-Type: application/json');

// Obtener parámetros de filtro
$categorias = isset($_GET['categorias']) ? explode(',', $_GET['categorias']) : [];
$precio_min = isset($_GET['precio_min']) ? floatval($_GET['precio_min']) : null;
$precio_max = isset($_GET['precio_max']) ? floatval($_GET['precio_max']) : null;
$rating_min = isset($_GET['rating_min']) ? floatval($_GET['rating_min']) : null;
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'reciente';

// Construir la consulta base
$sql = "SELECT s.idServicio, s.titulo, s.descripcion, s.precio, s.ubicacion,
               c.nombre as categoria, c.idCategoria,
               u.nombreCompleto as proveedor,
               (SELECT ruta FROM ImagenServicio WHERE idServicio = s.idServicio LIMIT 1) as imagen,
               AVG(r.calificacion) as calificacion,
               COUNT(r.idResena) as num_calificaciones,
               s.fechaPublicacion
        FROM Servicio s
        LEFT JOIN Categoria c ON s.idCategoria = c.idCategoria
        JOIN Proveedor p ON s.idProveedor = p.idProveedor
        JOIN Usuario u ON p.idProveedor = u.idUsuario
        LEFT JOIN Resena r ON s.idServicio = r.idServicio
        WHERE 1=1";

// Aplicar filtros
if (!empty($categorias)) {
    $categorias = array_map(function($id) use ($conn) {
        return $conn->real_escape_string($id);
    }, $categorias);
    $sql .= " AND c.idCategoria IN (" . implode(',', $categorias) . ")";
}

if ($precio_min !== null) {
    $sql .= " AND s.precio >= " . $precio_min;
}

if ($precio_max !== null) {
    $sql .= " AND s.precio <= " . $precio_max;
}

$sql .= " GROUP BY s.idServicio";

if ($rating_min !== null) {
    $sql .= " HAVING calificacion >= " . $rating_min;
}

// Aplicar ordenamiento
switch ($orden) {
    case 'reciente':
        $sql .= " ORDER BY s.fechaPublicacion DESC";
        break;
    case 'precio-asc':
        $sql .= " ORDER BY s.precio ASC";
        break;
    case 'precio-desc':
        $sql .= " ORDER BY s.precio DESC";
        break;
    case 'rating':
        $sql .= " ORDER BY calificacion DESC, num_calificaciones DESC";
        break;
    default:
        $sql .= " ORDER BY s.fechaPublicacion DESC";
}

$res = $conn->query($sql);
$servicios = [];

if ($res) {
    while ($row = $res->fetch_assoc()) {
        // Asegurarse de que los valores numéricos sean del tipo correcto
        $row['precio'] = floatval($row['precio']);
        $row['calificacion'] = floatval($row['calificacion'] ?? 0);
        $row['num_calificaciones'] = intval($row['num_calificaciones'] ?? 0);
        
        // Asegurarse de que la imagen tenga una ruta válida
        if ($row['imagen']) {
            $row['imagen'] = '../' . $row['imagen'];
        }
        
        $servicios[] = $row;
    }
}

echo json_encode($servicios);
?>
