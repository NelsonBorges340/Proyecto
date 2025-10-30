<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Verificar si es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'administrador') {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Listar categorías
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT c.*, 
            (SELECT COUNT(*) FROM Servicio WHERE idCategoria = c.idCategoria) as total_servicios 
            FROM Categoria c 
            ORDER BY c.nombre";
    $result = $conn->query($sql);
    $categorias = [];
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
    echo json_encode($categorias);
}

// Crear nueva categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $nombre = $data['nombre'];
    $descripcion = $data['descripcion'];

    $stmt = $conn->prepare("INSERT INTO Categoria (nombre, descripcion) VALUES (?, ?)");
    $stmt->bind_param("ss", $nombre, $descripcion);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['error' => $conn->error]);
    }
}

// Eliminar categoría
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];

    // Comenzar transacción
    $conn->begin_transaction();

    try {
        // Actualizar servicios para quitar la categoría
        $stmt = $conn->prepare("UPDATE Servicio SET idCategoria = NULL WHERE idCategoria = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Eliminar la categoría
        $stmt = $conn->prepare("DELETE FROM Categoria WHERE idCategoria = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>