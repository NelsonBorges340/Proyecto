<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar usuarios
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'buscar_usuarios') {
        $query = isset($_GET['q']) ? '%' . $_GET['q'] . '%' : '';
        
        $sql = "SELECT idUsuario, 
                nombreCompleto,
                rol 
                FROM Usuario 
                WHERE idUsuario != ? 
                AND (LOWER(nombreCompleto) LIKE LOWER(?) OR LOWER(email) LIKE LOWER(?))
                LIMIT 10";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usuario_id, $query, $query]);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }
        
        echo json_encode($usuarios);
        exit;
    }
}