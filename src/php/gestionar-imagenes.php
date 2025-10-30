<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Función para procesar y validar la imagen
function procesarImagen($file) {
    if ($file['error'] !== 0) {
        throw new Exception('Error al subir el archivo');
    }

    $allowed = ['image/jpeg', 'image/png', 'image/gif'];
    $tipo_mime = mime_content_type($file['tmp_name']);
    
    if (!in_array($tipo_mime, $allowed)) {
        throw new Exception('Tipo de archivo no permitido');
    }

    // Leer el contenido de la imagen
    $imagen = file_get_contents($file['tmp_name']);
    if ($imagen === false) {
        throw new Exception('Error al leer la imagen');
    }

    return [
        'contenido' => $imagen,
        'tipo_mime' => $tipo_mime
    ];
}

// Actualizar imagen de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo']) && $_POST['tipo'] === 'perfil') {
    try {
        if (!isset($_FILES['imagen'])) {
            throw new Exception('No se recibió ninguna imagen');
        }

        $imagen = procesarImagen($_FILES['imagen']);
        
        $stmt = $conn->prepare("UPDATE Usuario SET imagen_perfil = ? WHERE idUsuario = ?");
        $stmt->bind_param("bi", $imagen['contenido'], $_SESSION['usuario_id']);
        
        if (!$stmt->execute()) {
            throw new Exception('Error al guardar la imagen en la base de datos');
        }

        echo json_encode(['success' => true, 'message' => 'Imagen de perfil actualizada']);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Subir imagen de servicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo']) && $_POST['tipo'] === 'servicio') {
    try {
        if (!isset($_FILES['imagen']) || !isset($_POST['servicio_id'])) {
            throw new Exception('Faltan datos requeridos');
        }

        $servicio_id = $_POST['servicio_id'];
        $es_principal = isset($_POST['es_principal']) ? 1 : 0;

        // Verificar que el servicio pertenece al proveedor
        $stmt = $conn->prepare("SELECT idProveedor FROM Servicio WHERE idServicio = ?");
        $stmt->bind_param("i", $servicio_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $servicio = $result->fetch_assoc();

        if (!$servicio || $servicio['idProveedor'] !== $_SESSION['usuario_id']) {
            throw new Exception('No autorizado para este servicio');
        }

        $imagen = procesarImagen($_FILES['imagen']);

        $conn->begin_transaction();

        // Si es la imagen principal, desmarcar la anterior
        if ($es_principal) {
            $stmt = $conn->prepare("UPDATE ImagenServicio SET es_principal = 0 WHERE idServicio = ?");
            $stmt->bind_param("i", $servicio_id);
            $stmt->execute();
        }

        // Insertar la nueva imagen
        $stmt = $conn->prepare("INSERT INTO ImagenServicio (idServicio, imagen, tipo_mime, es_principal) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $servicio_id, $imagen['contenido'], $imagen['tipo_mime'], $es_principal);

        if (!$stmt->execute()) {
            throw new Exception('Error al guardar la imagen');
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Imagen de servicio guardada']);
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Eliminar imagen de servicio
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['imagen_id'])) {
            throw new Exception('ID de imagen no proporcionado');
        }

        $imagen_id = $data['imagen_id'];

        // Verificar autorización
        $stmt = $conn->prepare("
            SELECT s.idProveedor 
            FROM ImagenServicio i 
            JOIN Servicio s ON i.idServicio = s.idServicio 
            WHERE i.idImagen = ?
        ");
        $stmt->bind_param("i", $imagen_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $imagen = $result->fetch_assoc();

        if (!$imagen || $imagen['idProveedor'] !== $_SESSION['usuario_id']) {
            throw new Exception('No autorizado para eliminar esta imagen');
        }

        $stmt = $conn->prepare("DELETE FROM ImagenServicio WHERE idImagen = ?");
        $stmt->bind_param("i", $imagen_id);

        if (!$stmt->execute()) {
            throw new Exception('Error al eliminar la imagen');
        }

        echo json_encode(['success' => true, 'message' => 'Imagen eliminada']);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>