<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

$conn = getDB();
$usuario_id = $_SESSION['usuario_id'];

// Verificar si los usuarios son contactos
function sonContactos($conn, $usuario1_id, $usuario2_id) {
    $sql = "SELECT 1 FROM contactos_chat 
            WHERE (usuario1_id = ? AND usuario2_id = ?)
            OR (usuario1_id = ? AND usuario2_id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$usuario1_id, $usuario2_id, $usuario2_id, $usuario1_id]);
    return $stmt->rowCount() > 0;
}

// Obtener conversaciones del usuario
} 

// Enviar un mensaje
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['receptor_id']) || !isset($data['mensaje']) || empty(trim($data['mensaje']))) {
        echo json_encode(['error' => 'Faltan datos requeridos']);
        exit;
    }

    $receptor_id = $data['receptor_id'];
    $mensaje = trim($data['mensaje']);

    // Verificar si son contactos antes de permitir el envío de mensajes
    if (!sonContactos($conn, $usuario_id, $receptor_id)) {
        echo json_encode(['error' => 'Debes enviar una solicitud de chat primero']);
        exit;
    }

    try {
        $conn->beginTransaction();

        // Buscar o crear la conversación
        $sql_conv = "SELECT DISTINCT m.idConversacion 
                    FROM Mensaje m
                    WHERE (m.idRemitente = ? AND m.idDestinatario = ?) 
                    OR (m.idRemitente = ? AND m.idDestinatario = ?)";
        $stmt = $conn->prepare($sql_conv);
        $stmt->execute([$usuario_id, $receptor_id, $receptor_id, $usuario_id]);
        
        if ($stmt->rowCount() > 0) {
            $conversacion = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_conversacion = $conversacion['idConversacion'];
        } else {
            $sql_nueva_conv = "INSERT INTO Conversacion (fechaInicio) 
                             VALUES (NOW())";
            $stmt = $conn->prepare($sql_nueva_conv);
            $stmt->execute();
            $id_conversacion = $conn->lastInsertId();
        }

        // Insertar el mensaje
        $sql_mensaje = "INSERT INTO Mensaje (idConversacion, idRemitente, idDestinatario, contenido, horaEnvio) 
                       VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql_mensaje);
        $stmt->execute([$id_conversacion, $usuario_id, $mensaje]);

        $conn->commit();
        echo json_encode(['success' => true, 'conversacion_id' => $id_conversacion]);

    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Error al enviar mensaje: " . $e->getMessage());
        echo json_encode(['error' => 'Error al enviar el mensaje']);
    }
} 

elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'conversaciones') {
        $sql = "SELECT DISTINCT 
                    c.idConversacion,
                    c.fechaInicio,
                    u.nombreCompleto as nombreInterlocutor,
                    u.idUsuario as idInterlocutor,
                    (SELECT contenido 
                     FROM Mensaje 
                     WHERE idConversacion = c.idConversacion 
                     ORDER BY horaEnvio DESC 
                     LIMIT 1) as ultimoMensaje,
                    (SELECT horaEnvio 
                     FROM Mensaje 
                     WHERE idConversacion = c.idConversacion 
                     ORDER BY horaEnvio DESC 
                     LIMIT 1) as fechaUltimoMensaje
                FROM Conversacion c
                JOIN Mensaje m ON c.idConversacion = m.idConversacion
                JOIN Usuario u ON 
                    CASE 
                        WHEN m.idRemitente = ? THEN m.idDestinatario = u.idUsuario
                        ELSE m.idRemitente = u.idUsuario
                    END
                WHERE m.idRemitente = ? OR m.idDestinatario = ?
                GROUP BY c.idConversacion, u.idUsuario
                ORDER BY MAX(m.horaEnvio) DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usuario_id, $usuario_id, $usuario_id, $usuario_id]);
        $conversaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($conversaciones);
    }
    // Obtener mensajes de una conversación específica
    elseif ($_GET['action'] === 'mensajes' && isset($_GET['conversacion'])) {
        $id_conversacion = $_GET['conversacion'];
        
        // Verificar que el usuario sea parte de la conversación
        $sql_verificacion = "SELECT 1 FROM Mensaje 
                           WHERE idConversacion = ? 
                           AND (idRemitente = ? OR idDestinatario = ?)";
        $stmt = $conn->prepare($sql_verificacion);
        $stmt->execute([$id_conversacion, $usuario_id, $usuario_id]);
        
        if ($stmt->rowCount() === 0) {
            echo json_encode(['error' => 'No autorizado para ver esta conversación']);
            exit;
        }
        
        // Obtener los mensajes
        $sql = "SELECT m.*, 
                       u.nombreCompleto as nombreEmisor
                FROM Mensaje m
                JOIN Usuario u ON m.idRemitente = u.idUsuario
                WHERE m.idConversacion = ?
                ORDER BY m.horaEnvio ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_conversacion]);
        $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sql_verify = "SELECT 1 FROM Conversacion 
                      WHERE idConversacion = ? 
                      AND (idEmisor = ? OR idReceptor = ?)";
        $stmt = $conn->prepare($sql_verify);
        $stmt->bind_param("iii", $id_conversacion, $usuario_id, $usuario_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows === 0) {
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
        
        // Obtener mensajes
        $sql = "SELECT m.*, u.nombreCompleto as nombreEmisor
                FROM Mensaje m
                JOIN Usuario u ON m.idEmisor = u.idUsuario
                WHERE m.idConversacion = ?
                ORDER BY m.fechaEnvio ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_conversacion);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mensajes = [];
        while ($row = $result->fetch_assoc()) {
            $mensajes[] = $row;
        }
        
        echo json_encode($mensajes);
    }
}

// Enviar un nuevo mensaje
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['receptor_id'], $data['mensaje'])) {
        $receptor_id = $data['receptor_id'];
        $mensaje = $data['mensaje'];
        
        // Buscar si ya existe una conversación entre estos usuarios
        $sql = "SELECT idConversacion FROM Conversacion 
                WHERE (idEmisor = ? AND idReceptor = ?) 
                OR (idEmisor = ? AND idReceptor = ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $usuario_id, $receptor_id, $receptor_id, $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Usar conversación existente
            $conversacion = $result->fetch_assoc();
            $id_conversacion = $conversacion['idConversacion'];
        } else {
            // Crear nueva conversación
            $sql = "INSERT INTO Conversacion (idEmisor, idReceptor, fechaInicio) 
                    VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $usuario_id, $receptor_id);
            $stmt->execute();
            $id_conversacion = $conn->insert_id;
        }
        
        // Insertar el mensaje
        $sql = "INSERT INTO Mensaje (idConversacion, idEmisor, mensaje, fechaEnvio) 
                VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $id_conversacion, $usuario_id, $mensaje);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'mensaje' => 'Mensaje enviado correctamente',
                'id_mensaje' => $conn->insert_id
            ]);
        } else {
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al enviar el mensaje'
            ]);
        }
    } else {
        echo json_encode([
            'error' => true,
            'mensaje' => 'Faltan datos requeridos'
        ]);
    }
}
?>