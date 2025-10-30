<?php
require_once 'db.php';
header('Content-Type: application/json');

function read_json_body() {
    $raw = file_get_contents('php://input');
    if (!$raw) return null;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Listado de categorías con conteo de servicios
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
    exit;
}

if ($method === 'POST') {
    $data = read_json_body();
    if (!$data || !isset($data['action'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Falta "action" en el cuerpo JSON']);
        exit;
    }

    $action = $data['action'];

    if ($action === 'crear') {
        $nombre = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');

        if ($nombre === '') {
            http_response_code(400);
            echo json_encode(['error' => 'El nombre de la categoría es obligatorio']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO Categoria (nombre, descripcion) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $descripcion);
        $ok = $stmt->execute();

        echo json_encode(['success' => $ok, 'id' => $ok ? $stmt->insert_id : null]);
        exit;
    }

    if ($action === 'editar') {
        $id = intval($data['idCategoria'] ?? 0);
        $nombre = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');

        if ($id <= 0 || $nombre === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Datos inválidos para editar']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE Categoria SET nombre = ?, descripcion = ? WHERE idCategoria = ?");
        $stmt->bind_param("ssi", $nombre, $descripcion, $id);
        $ok = $stmt->execute();

        echo json_encode(['success' => $ok]);
        exit;
    }

    if ($action === 'eliminar') {
        $id = intval($data['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido para eliminar']);
            exit;
        }

        try {
            $conn->begin_transaction();

            // Desasociar servicios de la categoría a eliminar
            $stmt = $conn->prepare("UPDATE Servicio SET idCategoria = NULL WHERE idCategoria = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            // Eliminar la categoría
            $stmt = $conn->prepare("DELETE FROM Categoria WHERE idCategoria = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $conn->commit();
            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Action no reconocido']);
    exit;
}

if ($method === 'DELETE') {
    // Soporte alternativo: DELETE con JSON {id}
    $data = read_json_body();
    $id = intval($data['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID inválido para eliminar']);
        exit;
    }

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("UPDATE Servicio SET idCategoria = NULL WHERE idCategoria = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM Categoria WHERE idCategoria = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Throwable $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Si llega un método no soportado
http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
