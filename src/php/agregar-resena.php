<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servicio_id = $_POST['servicio_id'];
    $usuario_id = $_SESSION['usuario_id'];
    $calificacion = $_POST['calificacion'];
    $comentario = $_POST['comentario'];
    $fecha = date('Y-m-d');

    try {
        // Verificar si el usuario ya ha dejado una reseña para este servicio
        $check = $conn->prepare("SELECT idResena FROM Resena WHERE idCliente = ? AND idServicio = ?");
        $check->bind_param("ii", $usuario_id, $servicio_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            // Actualizar reseña existente
            $sql = "UPDATE Resena SET comentario = ?, calificacion = ?, fechaResena = ? 
                    WHERE idCliente = ? AND idServicio = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisii", $comentario, $calificacion, $fecha, $usuario_id, $servicio_id);
        } else {
            // Insertar nueva reseña
            $sql = "INSERT INTO Resena (idCliente, idServicio, comentario, calificacion, fechaResena) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisis", $usuario_id, $servicio_id, $comentario, $calificacion, $fecha);
        }

        if ($stmt->execute()) {
            header("Location: ../html/servicio.php?id=" . $servicio_id);
        } else {
            echo "Error al guardar la reseña: " . $stmt->error;
        }

    } catch(Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>