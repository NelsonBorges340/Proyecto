<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'proveedor') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servicio_id = $_POST['servicio_id'];
    $es_principal = isset($_POST['es_principal']) ? 1 : 0;
    
    // Verificar que el servicio pertenece al proveedor
    $stmt = $conn->prepare("SELECT idProveedor FROM Servicio WHERE idServicio = ?");
    $stmt->bind_param("i", $servicio_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $servicio = $result->fetch_assoc();
    
    if (!$servicio || $servicio['idProveedor'] !== $_SESSION['usuario_id']) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Servicio no encontrado o no autorizado']);
        exit;
    }

    // Procesar la imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['imagen']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Crear directorio si no existe
            $target_dir = "../img/servicios/" . $servicio_id . "/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // Generar nombre único para la imagen
            $new_filename = uniqid('servicio_') . "." . $ext;
            $target_file = $target_dir . $new_filename;
            $ruta_imagen = "img/servicios/" . $servicio_id . "/" . $new_filename;

            try {
                $conn->begin_transaction();

                // Si es la imagen principal, desmarcar la anterior
                if ($es_principal) {
                    $sql = "UPDATE ImagenServicio SET es_principal = 0 WHERE idServicio = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $servicio_id);
                    $stmt->execute();
                }

                // Obtener el siguiente orden
                $sql = "SELECT COALESCE(MAX(orden), 0) + 1 AS siguiente_orden 
                        FROM ImagenServicio WHERE idServicio = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $servicio_id);
                $stmt->execute();
                $orden = $stmt->get_result()->fetch_assoc()['siguiente_orden'];

                // Insertar la nueva imagen
                $sql = "INSERT INTO ImagenServicio (idServicio, ruta_imagen, orden, es_principal) 
                        VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isii", $servicio_id, $ruta_imagen, $orden, $es_principal);

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file) && $stmt->execute()) {
                    $conn->commit();
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Imagen subida correctamente',
                        'ruta' => $ruta_imagen
                    ]);
                } else {
                    throw new Exception("Error al subir la imagen");
                }
            } catch (Exception $e) {
                $conn->rollback();
                if (file_exists($target_file)) {
                    unlink($target_file);
                }
                header('Content-Type: application/json');
                echo json_encode(['error' => $e->getMessage()]);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Tipo de archivo no permitido']);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No se recibió ninguna imagen']);
    }
}

// Para eliminar una imagen
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $imagen_id = $data['imagen_id'] ?? null;
    
    if ($imagen_id) {
        try {
            // Verificar que la imagen pertenece a un servicio del proveedor
            $sql = "SELECT i.ruta_imagen, i.idServicio, s.idProveedor 
                    FROM ImagenServicio i
                    JOIN Servicio s ON i.idServicio = s.idServicio
                    WHERE i.idImagen = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $imagen_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $imagen = $result->fetch_assoc();

            if (!$imagen || $imagen['idProveedor'] !== $_SESSION['usuario_id']) {
                throw new Exception('No autorizado para eliminar esta imagen');
            }

            $conn->begin_transaction();

            // Eliminar el registro de la base de datos
            $sql = "DELETE FROM ImagenServicio WHERE idImagen = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $imagen_id);
            
            if ($stmt->execute()) {
                // Eliminar el archivo físico
                $ruta_completa = "../" . $imagen['ruta_imagen'];
                if (file_exists($ruta_completa)) {
                    unlink($ruta_completa);
                }
                
                $conn->commit();
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Error al eliminar la imagen');
            }
        } catch (Exception $e) {
            $conn->rollback();
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>