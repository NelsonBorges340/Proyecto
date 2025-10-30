<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../html/finicio.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servicio_id = $_POST['servicio_id'];
    $calificacion = $_POST['calificacion'];
    $comentario = $_POST['comentario'];
    $usuario_id = $_SESSION['usuario_id'];
    $fecha = date('Y-m-d');

    try {
        // Verificar si el usuario ya ha reseñado este servicio
        $stmt = $conn->prepare("SELECT idResena FROM Resena WHERE idCliente = ? AND idServicio = ?");
        $stmt->bind_param("ii", $usuario_id, $servicio_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Actualizar reseña existente
            $stmt = $conn->prepare("UPDATE Resena SET comentario = ?, calificacion = ?, fechaResena = ? WHERE idCliente = ? AND idServicio = ?");
            $stmt->bind_param("sisii", $comentario, $calificacion, $fecha, $usuario_id, $servicio_id);
        } else {
            // Insertar nueva reseña
            $stmt = $conn->prepare("INSERT INTO Resena (idCliente, idServicio, comentario, calificacion, fechaResena) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisis", $usuario_id, $servicio_id, $comentario, $calificacion, $fecha);
        }

        if ($stmt->execute()) {
            // Actualizar el promedio de calificaciones en cache si lo necesitas
            echo json_encode(['success' => true]);
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $servicio_id = $_GET['servicio_id'];
    
    try {
        // Obtener reseñas con información del usuario
        $sql = "SELECT r.*, u.nombreCompleto, 
                (SELECT COUNT(*) FROM Resena WHERE idServicio = r.idServicio) as total_resenas,
                (SELECT AVG(calificacion) FROM Resena WHERE idServicio = r.idServicio) as promedio
                FROM Resena r
                JOIN Usuario u ON r.idCliente = u.idUsuario
                WHERE r.idServicio = ?
                ORDER BY r.fechaResena DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $servicio_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $resenas = [];
        
        while ($row = $result->fetch_assoc()) {
            $resenas[] = $row;
        }
        
        echo json_encode($resenas);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>