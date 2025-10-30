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

function obtenerUsuario(PDO $conn, int $id): ?array {
    $stmt = $conn->prepare('SELECT idUsuario, nombreCompleto, rol FROM Usuario WHERE idUsuario = ? LIMIT 1');
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    return $usuario ?: null;
}

function sonContactos(PDO $conn, int $usuarioA, int $usuarioB): bool {
    [$u1, $u2] = normalizarPar($usuarioA, $usuarioB);
    $stmt = $conn->prepare('SELECT id FROM contactos_chat WHERE usuario1_id = ? AND usuario2_id = ? LIMIT 1');
    $stmt->execute([$u1, $u2]);
    return (bool) $stmt->fetch();
}

function obtenerChatId(PDO $conn, int $usuarioA, int $usuarioB, bool $crear = false): ?int {
    [$u1, $u2] = normalizarPar($usuarioA, $usuarioB);

    $stmt = $conn->prepare('SELECT id FROM chats WHERE user1_id = ? AND user2_id = ? LIMIT 1');
    $stmt->execute([$u1, $u2]);
    $id = $stmt->fetchColumn();
    if ($id) {
        return (int) $id;
    }

    // Compatibilidad con datos antiguos en orden inverso.
    $stmt = $conn->prepare('SELECT id FROM chats WHERE user1_id = ? AND user2_id = ? LIMIT 1');
    $stmt->execute([$u2, $u1]);
    $id = $stmt->fetchColumn();
    if ($id) {
        return (int) $id;
    }

    if (!$crear) {
        return null;
    }

    $stmt = $conn->prepare('INSERT INTO chats (user1_id, user2_id) VALUES (?, ?)');
    $stmt->execute([$u1, $u2]);
    return (int) $conn->lastInsertId();
}

function obtenerContactos(PDO $conn, int $usuarioId): array {
    $sql = "
        SELECT 
            cc.id,
            CASE WHEN cc.usuario1_id = :usuario THEN cc.usuario2_id ELSE cc.usuario1_id END AS contacto_id,
            u.nombreCompleto,
            u.rol,
            chat.id AS chat_id,
            chat.last_message_at,
            (
                SELECT m.message 
                FROM mensajes m 
                WHERE m.chat_id = chat.id 
                ORDER BY m.created_at DESC 
                LIMIT 1
            ) AS last_message,
            (
                SELECT COUNT(*) 
                FROM mensajes m 
                WHERE m.chat_id = chat.id 
                  AND m.sender_id != :usuario 
                  AND m.is_read = 0
            ) AS unread_count
        FROM contactos_chat cc
        JOIN Usuario u ON u.idUsuario = CASE WHEN cc.usuario1_id = :usuario THEN cc.usuario2_id ELSE cc.usuario1_id END
        LEFT JOIN chats chat ON (
            (chat.user1_id = :usuario AND chat.user2_id = u.idUsuario)
            OR
            (chat.user2_id = :usuario AND chat.user1_id = u.idUsuario)
        )
        WHERE cc.usuario1_id = :usuario OR cc.usuario2_id = :usuario
        ORDER BY COALESCE(chat.last_message_at, cc.fecha_inicio) DESC, u.nombreCompleto ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario' => $usuarioId]);
    $contactos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($contactos as &$contacto) {
        $contacto['contacto_id'] = (int) $contacto['contacto_id'];
        $contacto['chat_id'] = isset($contacto['chat_id']) ? (int) $contacto['chat_id'] : null;
        $contacto['unread_count'] = isset($contacto['unread_count']) ? (int) $contacto['unread_count'] : 0;
    }

    return $contactos;
}

function obtenerSolicitudesEntrantes(PDO $conn, int $usuarioId): array {
    $stmt = $conn->prepare("
        SELECT s.id, s.emisor_id, s.fecha_solicitud, u.nombreCompleto, u.rol
        FROM solicitud_chat s
        JOIN Usuario u ON u.idUsuario = s.emisor_id
        WHERE s.receptor_id = ? AND s.estado = 'pendiente'
        ORDER BY s.fecha_solicitud DESC
    ");
    $stmt->execute([$usuarioId]);
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($solicitudes as &$solicitud) {
        $solicitud['id'] = (int) $solicitud['id'];
        $solicitud['emisor_id'] = (int) $solicitud['emisor_id'];
    }

    return $solicitudes;
}

function obtenerSolicitudesEnviadas(PDO $conn, int $usuarioId): array {
    $stmt = $conn->prepare("
        SELECT s.id, s.receptor_id, s.fecha_solicitud, u.nombreCompleto, u.rol
        FROM solicitud_chat s
        JOIN Usuario u ON u.idUsuario = s.receptor_id
        WHERE s.emisor_id = ? AND s.estado = 'pendiente'
        ORDER BY s.fecha_solicitud DESC
    ");
    $stmt->execute([$usuarioId]);
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($solicitudes as &$solicitud) {
        $solicitud['id'] = (int) $solicitud['id'];
        $solicitud['receptor_id'] = (int) $solicitud['receptor_id'];
    }

    return $solicitudes;
}

function buscarUsuarios(PDO $conn, int $usuarioId, string $busqueda): array {
    $sql = "
        SELECT 
            u.idUsuario,
            u.nombreCompleto,
            u.rol,
            CASE 
                WHEN cc.id IS NOT NULL THEN 'contacto'
                WHEN sc_rec.id IS NOT NULL THEN 'pendiente_recibida'
                WHEN sc_env.id IS NOT NULL THEN 'pendiente_enviada'
                ELSE 'ninguno'
            END AS estado,
            sc_rec.id AS solicitud_recibida_id
        FROM Usuario u
        LEFT JOIN contactos_chat cc 
            ON (
                (cc.usuario1_id = :usuario AND cc.usuario2_id = u.idUsuario)
                OR
                (cc.usuario2_id = :usuario AND cc.usuario1_id = u.idUsuario)
            )
        LEFT JOIN solicitud_chat sc_env 
            ON sc_env.emisor_id = :usuario AND sc_env.receptor_id = u.idUsuario AND sc_env.estado = 'pendiente'
        LEFT JOIN solicitud_chat sc_rec 
            ON sc_rec.emisor_id = u.idUsuario AND sc_rec.receptor_id = :usuario AND sc_rec.estado = 'pendiente'
        WHERE u.idUsuario != :usuario
          AND (u.nombreCompleto LIKE :busqueda OR u.email LIKE :busqueda)
        ORDER BY u.nombreCompleto ASC
        LIMIT 15
    ";

    $stmt = $conn->prepare($sql);
    $like = '%' . $busqueda . '%';
    $stmt->execute([
        ':usuario' => $usuarioId,
        ':busqueda' => $like,
    ]);

    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($usuarios as &$usuario) {
        $usuario['idUsuario'] = (int) $usuario['idUsuario'];
        $usuario['solicitud_recibida_id'] = isset($usuario['solicitud_recibida_id'])
            ? (int) $usuario['solicitud_recibida_id']
            : null;
    }

    return $usuarios;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'bootstrap';

    try {
        switch ($action) {
            case 'bootstrap':
                $usuario = obtenerUsuario($conn, $usuarioId);
                if (!$usuario) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Usuario no encontrado']);
                    exit;
                }

                echo json_encode([
                    'user' => $usuario,
                    'contacts' => obtenerContactos($conn, $usuarioId),
                    'incoming_requests' => obtenerSolicitudesEntrantes($conn, $usuarioId),
                    'outgoing_requests' => obtenerSolicitudesEnviadas($conn, $usuarioId),
                ]);
                exit;

            case 'messages':
                if (!isset($_GET['chat_id'])) {
                    http_response_code(422);
                    echo json_encode(['error' => 'chat_id es requerido']);
                    exit;
                }
                $chatId = (int) $_GET['chat_id'];
                $stmt = $conn->prepare('SELECT id, user1_id, user2_id FROM chats WHERE id = ? LIMIT 1');
                $stmt->execute([$chatId]);
                $chat = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$chat || (($chat['user1_id'] != $usuarioId) && ($chat['user2_id'] != $usuarioId))) {
                    http_response_code(403);
                    echo json_encode(['error' => 'No autorizado para ver esta conversación']);
                    exit;
                }

                $otroUsuarioId = $chat['user1_id'] == $usuarioId ? (int) $chat['user2_id'] : (int) $chat['user1_id'];

                $stmt = $conn->prepare('
                    SELECT m.id, m.sender_id, m.message, m.created_at, m.is_read, u.nombreCompleto AS sender_name
                    FROM mensajes m
                    JOIN Usuario u ON u.idUsuario = m.sender_id
                    WHERE m.chat_id = ?
                    ORDER BY m.created_at ASC
                ');
                $stmt->execute([$chatId]);
                $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($mensajes as &$mensaje) {
                    $mensaje['id'] = (int) $mensaje['id'];
                    $mensaje['sender_id'] = (int) $mensaje['sender_id'];
                    $mensaje['is_read'] = (bool) $mensaje['is_read'];
                    $mensaje['isMine'] = $mensaje['sender_id'] === $usuarioId;
                }

                $stmt = $conn->prepare('UPDATE mensajes SET is_read = 1 WHERE chat_id = ? AND sender_id = ?');
                $stmt->execute([$chatId, $otroUsuarioId]);

                echo json_encode([
                    'messages' => $mensajes,
                    'chat' => [
                        'id' => $chatId,
                        'other_user_id' => $otroUsuarioId,
                    ],
                ]);
                exit;

            case 'search_users':
                if (!isset($_GET['q']) || strlen(trim($_GET['q'])) < 2) {
                    echo json_encode(['users' => []]);
                    exit;
                }
                $query = trim($_GET['q']);
                echo json_encode(['users' => buscarUsuarios($conn, $usuarioId, $query)]);
                exit;

            case 'get_chat':
                if (!isset($_GET['contact_id'])) {
                    http_response_code(422);
                    echo json_encode(['error' => 'contact_id es requerido']);
                    exit;
                }
                $contactId = (int) $_GET['contact_id'];
                if (!sonContactos($conn, $usuarioId, $contactId)) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Debe aceptar la solicitud antes de iniciar el chat']);
                    exit;
                }
                $chatId = obtenerChatId($conn, $usuarioId, $contactId, true);
                $contacto = obtenerUsuario($conn, $contactId);
                echo json_encode([
                    'chat_id' => $chatId,
                    'contact' => $contacto,
                ]);
                exit;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Acción no válida']);
                exit;
        }
    } catch (PDOException $e) {
        error_log('Error en chat.php (GET): ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error interno del servidor']);
        exit;
    }
}

if ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $payload['action'] ?? '';

    try {
        switch ($action) {
            case 'send_message':
                $mensaje = isset($payload['message']) ? trim($payload['message']) : '';
                $chatId = isset($payload['chat_id']) ? (int) $payload['chat_id'] : null;
                $contactId = isset($payload['contact_id']) ? (int) $payload['contact_id'] : null;

                if ($mensaje === '') {
                    http_response_code(422);
                    echo json_encode(['error' => 'El mensaje no puede estar vacío']);
                    exit;
                }

                if ($chatId === null && $contactId === null) {
                    http_response_code(422);
                    echo json_encode(['error' => 'Debe indicar chat_id o contact_id']);
                    exit;
                }

                if ($chatId !== null) {
                    $stmt = $conn->prepare('SELECT user1_id, user2_id FROM chats WHERE id = ? LIMIT 1');
                    $stmt->execute([$chatId]);
                    $chat = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$chat || (($chat['user1_id'] != $usuarioId) && ($chat['user2_id'] != $usuarioId))) {
                        http_response_code(403);
                        echo json_encode(['error' => 'No autorizado para enviar mensajes en este chat']);
                        exit;
                    }
                    $contactId = $chat['user1_id'] == $usuarioId ? (int) $chat['user2_id'] : (int) $chat['user1_id'];
                } else {
                    if (!sonContactos($conn, $usuarioId, $contactId)) {
                        http_response_code(403);
                        echo json_encode(['error' => 'Debes ser contacto para enviar mensajes']);
                        exit;
                    }
                    $chatId = obtenerChatId($conn, $usuarioId, $contactId, true);
                }

                $stmt = $conn->prepare('INSERT INTO mensajes (chat_id, sender_id, message) VALUES (?, ?, ?)');
                $stmt->execute([$chatId, $usuarioId, $mensaje]);
                $mensajeId = (int) $conn->lastInsertId();

                $stmt = $conn->prepare('UPDATE chats SET last_message_at = NOW() WHERE id = ?');
                $stmt->execute([$chatId]);

                $stmt = $conn->prepare('SELECT created_at FROM mensajes WHERE id = ? LIMIT 1');
                $stmt->execute([$mensajeId]);
                $fecha = $stmt->fetchColumn();

                echo json_encode([
                    'success' => true,
                    'message' => [
                        'id' => $mensajeId,
                        'chat_id' => $chatId,
                        'sender_id' => $usuarioId,
                        'message' => $mensaje,
                        'created_at' => $fecha,
                    ],
                ]);
                exit;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Acción no válida']);
                exit;
        }
    } catch (PDOException $e) {
        error_log('Error en chat.php (POST): ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error interno del servidor']);
        exit;
    }
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
