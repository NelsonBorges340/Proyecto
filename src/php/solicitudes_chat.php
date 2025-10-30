<?php
session_start();

require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$conn = getDB();
$usuarioId = (int) $_SESSION['usuario_id'];

function normalizarPar(int $a, int $b): array {
    return $a < $b ? [$a, $b] : [$b, $a];
}

/**
 * Garantiza que exista un registro de chat para el par de usuarios.
 */
function asegurarChat(PDO $conn, int $usuarioA, int $usuarioB): int {
    [$u1, $u2] = normalizarPar($usuarioA, $usuarioB);

    $stmt = $conn->prepare('SELECT id FROM chats WHERE user1_id = ? AND user2_id = ? LIMIT 1');
    $stmt->execute([$u1, $u2]);
    $chatId = $stmt->fetchColumn();
    if ($chatId) {
        return (int) $chatId;
    }

    // Compatibilidad con datos antiguos en orden inverso.
    $stmt = $conn->prepare('SELECT id FROM chats WHERE user1_id = ? AND user2_id = ? LIMIT 1');
    $stmt->execute([$u2, $u1]);
    $chatId = $stmt->fetchColumn();
    if ($chatId) {
        return (int) $chatId;
    }

    $stmt = $conn->prepare('INSERT INTO chats (user1_id, user2_id) VALUES (?, ?)');
    $stmt->execute([$u1, $u2]);

    return (int) $conn->lastInsertId();
}

function crearSolicitud(PDO $conn, int $emisorId, int $receptorId): array {
    if ($emisorId === $receptorId) {
        return ['error' => 'No puedes enviarte una solicitud a ti mismo'];
    }

    try {
        $stmt = $conn->prepare('SELECT idUsuario, nombreCompleto FROM Usuario WHERE idUsuario = ? LIMIT 1');
        $stmt->execute([$receptorId]);
        $receptor = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$receptor) {
            return ['error' => 'El usuario seleccionado no existe'];
        }

        [$u1, $u2] = normalizarPar($emisorId, $receptorId);
        $stmt = $conn->prepare('SELECT id FROM contactos_chat WHERE usuario1_id = ? AND usuario2_id = ? LIMIT 1');
        $stmt->execute([$u1, $u2]);
        if ($stmt->fetch()) {
            return ['error' => 'Ya son contactos'];
        }

        $stmt = $conn->prepare("SELECT id FROM solicitud_chat WHERE emisor_id = ? AND receptor_id = ? AND estado = 'pendiente' LIMIT 1");
        $stmt->execute([$receptorId, $emisorId]);
        $solicitudRecibida = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($solicitudRecibida) {
            return [
                'error' => 'El usuario ya te envió una solicitud pendiente',
                'tipo' => 'pendiente_recibida',
                'solicitud_id' => (int) $solicitudRecibida['id'],
            ];
        }

        $stmt = $conn->prepare('SELECT id, estado FROM solicitud_chat WHERE emisor_id = ? AND receptor_id = ? LIMIT 1');
        $stmt->execute([$emisorId, $receptorId]);
        $existente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existente) {
            if ($existente['estado'] === 'pendiente') {
                return ['error' => 'Ya enviaste una solicitud pendiente'];
            }

            $stmt = $conn->prepare('UPDATE solicitud_chat SET estado = \'pendiente\', fecha_solicitud = NOW(), fecha_respuesta = NULL WHERE id = ?');
            $stmt->execute([$existente['id']]);

            return [
                'success' => true,
                'message' => 'Solicitud reenviada',
                'solicitud_id' => (int) $existente['id'],
                'receptor' => [
                    'id' => (int) $receptor['idUsuario'],
                    'nombre' => $receptor['nombreCompleto'],
                ],
            ];
        }

        $stmt = $conn->prepare('INSERT INTO solicitud_chat (emisor_id, receptor_id) VALUES (?, ?)');
        $stmt->execute([$emisorId, $receptorId]);
        $solicitudId = (int) $conn->lastInsertId();

        return [
            'success' => true,
            'message' => 'Solicitud enviada correctamente',
            'solicitud_id' => $solicitudId,
            'receptor' => [
                'id' => (int) $receptor['idUsuario'],
                'nombre' => $receptor['nombreCompleto'],
            ],
        ];
    } catch (PDOException $e) {
        error_log('Error en crearSolicitud: ' . $e->getMessage());
        return ['error' => 'No se pudo enviar la solicitud'];
    }
}

function aceptarSolicitud(PDO $conn, int $solicitudId, int $receptorId): array {
    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare('SELECT id, emisor_id, estado FROM solicitud_chat WHERE id = ? AND receptor_id = ? FOR UPDATE');
        $stmt->execute([$solicitudId, $receptorId]);
        $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$solicitud) {
            $conn->rollBack();
            return ['error' => 'Solicitud no encontrada'];
        }
        if ($solicitud['estado'] !== 'pendiente') {
            $conn->rollBack();
            return ['error' => 'La solicitud ya fue gestionada'];
        }

        $stmt = $conn->prepare('UPDATE solicitud_chat SET estado = \'aceptada\', fecha_respuesta = NOW() WHERE id = ?');
        $stmt->execute([$solicitudId]);

        [$u1, $u2] = normalizarPar((int) $solicitud['emisor_id'], $receptorId);
        $stmt = $conn->prepare('INSERT IGNORE INTO contactos_chat (usuario1_id, usuario2_id, fecha_inicio) VALUES (?, ?, NOW())');
        $stmt->execute([$u1, $u2]);

        $chatId = asegurarChat($conn, (int) $solicitud['emisor_id'], $receptorId);

        $stmt = $conn->prepare('SELECT idUsuario, nombreCompleto, rol FROM Usuario WHERE idUsuario = ? LIMIT 1');
        $stmt->execute([$solicitud['emisor_id']]);
        $contacto = $stmt->fetch(PDO::FETCH_ASSOC);

        $conn->commit();

        return [
            'success' => true,
            'message' => 'Solicitud aceptada',
            'chat_id' => $chatId,
            'contacto' => [
                'id' => isset($contacto['idUsuario']) ? (int) $contacto['idUsuario'] : (int) $solicitud['emisor_id'],
                'nombre' => $contacto['nombreCompleto'] ?? '',
                'rol' => $contacto['rol'] ?? '',
            ],
        ];
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log('Error en aceptarSolicitud: ' . $e->getMessage());
        return ['error' => 'No se pudo aceptar la solicitud'];
    }
}

function rechazarSolicitud(PDO $conn, int $solicitudId, int $receptorId): array {
    try {
        $stmt = $conn->prepare('UPDATE solicitud_chat SET estado = \'rechazada\', fecha_respuesta = NOW() WHERE id = ? AND receptor_id = ? AND estado = \'pendiente\'');
        $stmt->execute([$solicitudId, $receptorId]);

        if ($stmt->rowCount() === 0) {
            return ['error' => 'No se pudo rechazar la solicitud'];
        }

        return [
            'success' => true,
            'message' => 'Solicitud rechazada',
        ];
    } catch (PDOException $e) {
        error_log('Error en rechazarSolicitud: ' . $e->getMessage());
        return ['error' => 'No se pudo rechazar la solicitud'];
    }
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $accion = $data['action'] ?? '';

    switch ($accion) {
        case 'crear':
            if (!isset($data['receptor_id'])) {
                http_response_code(422);
                echo json_encode(['error' => 'Falta el ID del receptor']);
                break;
            }
            $respuesta = crearSolicitud($conn, $usuarioId, (int) $data['receptor_id']);
            http_response_code(isset($respuesta['error']) ? 400 : 200);
            echo json_encode($respuesta);
            break;

        case 'aceptar':
            if (!isset($data['solicitud_id'])) {
                http_response_code(422);
                echo json_encode(['error' => 'Falta el ID de la solicitud']);
                break;
            }
            $respuesta = aceptarSolicitud($conn, (int) $data['solicitud_id'], $usuarioId);
            http_response_code(isset($respuesta['error']) ? 400 : 200);
            echo json_encode($respuesta);
            break;

        case 'rechazar':
            if (!isset($data['solicitud_id'])) {
                http_response_code(422);
                echo json_encode(['error' => 'Falta el ID de la solicitud']);
                break;
            }
            $respuesta = rechazarSolicitud($conn, (int) $data['solicitud_id'], $usuarioId);
            http_response_code(isset($respuesta['error']) ? 400 : 200);
            echo json_encode($respuesta);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
    exit;
}

if ($method === 'GET') {
    $accion = $_GET['action'] ?? '';

    if ($accion === 'pendientes') {
        try {
            $stmt = $conn->prepare("
                SELECT s.id, s.emisor_id, s.fecha_solicitud, u.nombreCompleto, u.email, u.rol
                FROM solicitud_chat s
                JOIN Usuario u ON u.idUsuario = s.emisor_id
                WHERE s.receptor_id = ? AND s.estado = 'pendiente'
                ORDER BY s.fecha_solicitud DESC
            ");
            $stmt->execute([$usuarioId]);
            echo json_encode([
                'success' => true,
                'solicitudes' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            ]);
        } catch (PDOException $e) {
            error_log('Error al obtener solicitudes pendientes: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'No se pudieron obtener las solicitudes']);
        }
        exit;
    }

    if ($accion === 'enviadas') {
        try {
            $stmt = $conn->prepare("
                SELECT s.id, s.receptor_id, s.estado, s.fecha_solicitud, u.nombreCompleto, u.email, u.rol
                FROM solicitud_chat s
                JOIN Usuario u ON u.idUsuario = s.receptor_id
                WHERE s.emisor_id = ? AND s.estado = 'pendiente'
                ORDER BY s.fecha_solicitud DESC
            ");
            $stmt->execute([$usuarioId]);
            echo json_encode([
                'success' => true,
                'solicitudes' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            ]);
        } catch (PDOException $e) {
            error_log('Error al obtener solicitudes enviadas: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'No se pudieron obtener las solicitudes enviadas']);
        }
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Acción no especificada']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
