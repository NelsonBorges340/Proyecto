<?php
include 'db.php';
header('Content-Type: application/json');

// Obtener proveedores con mejores calificaciones promedio
$sql = "SELECT 
            p.idProveedor,
            u.nombreCompleto,
            p.nombreNegocio,
            p.descripcion,
            p.especialidad,
            COUNT(DISTINCT s.idServicio) as total_servicios,
            COUNT(DISTINCT r.idResena) as total_resenas,
            AVG(r.calificacion) as promedio_calificacion
        FROM Proveedor p
        JOIN Usuario u ON p.idProveedor = u.idUsuario
        LEFT JOIN Servicio s ON p.idProveedor = s.idProveedor
        LEFT JOIN Resena r ON s.idServicio = r.idServicio
        GROUP BY p.idProveedor
        ORDER BY promedio_calificacion DESC, total_resenas DESC
        LIMIT 10";

$res = $conn->query($sql);
$proveedores = [];
while ($row = $res->fetch_assoc()) {
    $row['promedio_calificacion'] = $row['promedio_calificacion'] ?? 0;
    $row['total_resenas'] = $row['total_resenas'] ?? 0;
    $proveedores[] = $row;
}

echo json_encode($proveedores);
?>