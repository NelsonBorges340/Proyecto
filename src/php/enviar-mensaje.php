<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idRemitente = $_SESSION['usuario_id'];
    $idDestinatario = $_POST['destinatario_id'];
    $contenido = $_POST['contenido'];
    $idConversacion = $_POST['idConversacion'];

    $sql = "INSERT INTO Mensaje (idConversacion, idRemitente, idDestinatario, contenido) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $idConversacion, $idRemitente, $idDestinatario, $contenido);

    if ($stmt->execute()) {
        echo "ok";
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}
?>