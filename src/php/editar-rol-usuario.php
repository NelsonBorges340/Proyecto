<?php
session_start();
require_once 'db.php';

// Verificar si es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'administrador') {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
    $nuevoRol = $data['rol'];

    $conn->begin_transaction();

    try {
        // Primero actualizamos el rol en la tabla Usuario
        $stmt = $conn->prepare("UPDATE Usuario SET rol = ? WHERE idUsuario = ?");
        $stmt->bind_param("si", $nuevoRol, $id);
        $stmt->execute();

        // Eliminamos cualquier rol existente
        $conn->query("DELETE FROM Cliente WHERE idCliente = $id");
        $conn->query("DELETE FROM Proveedor WHERE idProveedor = $id");
        $conn->query("DELETE FROM Administrador WHERE idAdministrador = $id");

        // Insertamos en la tabla correspondiente según el nuevo rol
        switch ($nuevoRol) {
            case 'cliente':
                $conn->query("INSERT INTO Cliente (idCliente) VALUES ($id)");
                break;
            case 'proveedor':
                $conn->query("INSERT INTO Proveedor (idProveedor, nombreNegocio, descripcion, especialidad) 
                            VALUES ($id, 'Nuevo Negocio', 'Descripción pendiente', 'Especialidad pendiente')");
                break;
            case 'administrador':
                $conn->query("INSERT INTO Administrador (idAdministrador) VALUES ($id)");
                break;
        }

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['error' => $e->getMessage()]);
    }
}