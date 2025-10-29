<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once (__DIR__."/../conexion.php");

$IDemisor   = $_SESSION['usuario'] ?? null;
$IDreceptor = $_POST['IDreceptor'] ?? null;
$Mensaje    = trim($_POST['Mensaje'] ?? '');

if($IDemisor && $IDreceptor && !empty($Mensaje)){
    // Primero verificamos si existe un mensaje idéntico enviado en los últimos segundos
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Mensajes 
        WHERE IDemisor = ? 
        AND IDreceptor = ? 
        AND Mensaje = ? 
        AND Fecha_envio >= DATE_SUB(NOW(), INTERVAL 2 SECOND)");
    
    $stmt->execute([$IDemisor, $IDreceptor, $Mensaje]);
    $existe = $stmt->fetchColumn();

    if($existe == 0) {
        // Solo si no existe un mensaje idéntico reciente, lo insertamos
        $stmt = $pdo->prepare("INSERT INTO Mensajes (IDemisor, IDreceptor, Mensaje, Fecha_envio) 
            VALUES (?, ?, ?, NOW())");
        $stmt->execute([$IDemisor, $IDreceptor, $Mensaje]);
        echo "ok";
    } else {
        echo "duplicate";
    }
} else {
    echo "error";
}
?>