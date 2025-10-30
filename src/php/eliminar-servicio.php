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
$id = 0;

if ($method === 'POST' || $method === 'DELETE') {
    $payload = read_json_body();
    if ($payload && isset($payload['id'])) {
        $id = intval($payload['id']);
    }
}

if ($id <= 0 && isset($_GET['id'])) {
    $id = intval($_GET['id']);
}

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de servicio no válido']);
    exit;
}

try {
    // Si hay dependencias, podés sumar deletes/updates acá.
    // $conn->begin_transaction(); // si querés atomicidad

    $stmt = $conn->prepare("DELETE FROM Servicio WHERE idServicio = ?");
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();

    // $conn->commit();

    if ($ok && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Servicio eliminado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró el servicio o no se pudo eliminar']);
    }
} catch (Throwable $e) {
    // $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el servicio: ' . $e->getMessage()]);
}
