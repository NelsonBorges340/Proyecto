<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../conexion.php";

$usuarioID = $_SESSION['usuario'] ?? null; // Change from IDusuario to usuario
$chatID    = $_GET['chat_id'] ?? null;

if($usuarioID && $chatID){
    $stmt = $pdo->prepare("SELECT * FROM Mensajes
        WHERE (IDemisor = ? AND IDreceptor = ?)
           OR (IDemisor = ? AND IDreceptor = ?)
        ORDER BY Fecha_envio ASC
    ");
    $stmt->execute([$usuarioID, $chatID, $chatID, $usuarioID]);
    $mensajes = $stmt->fetchAll();

    foreach($mensajes as $msg){
        $clase = ($msg['IDemisor'] == $usuarioID) ? 'usuario' : 'contacto'; // Change classes to match CSS
        echo "<div class='mensaje $clase'>".htmlspecialchars($msg['Mensaje'])."</div>";
    }
}
?>
