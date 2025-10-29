<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once (__DIR__."/../Conexion.php");

switch ($_POST['Select']) {
    case 'EnviarMensaje':
 
#region EnviarMensaje
        // Código para enviar mensaje
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
    #endregion
        break;


    case 'RecibirMensaje':

 #region RecibirMensaje
 $usuarioID = $_SESSION['usuario'] ?? null; // Change from IDusuario to usuario
 $chatID    = $_POST['chat_id'] ?? null;

if($usuarioID && $chatID){
    $stmt = $pdo->prepare("SELECT * FROM Mensajes
        WHERE (IDemisor = ? AND IDreceptor = ?)
           OR (IDemisor = ? AND IDreceptor = ?)
        ORDER BY Fecha_envio ASC
    ");
    
    $stmt->execute([$usuarioID, $chatID, $chatID, $usuarioID]);
    $mensajes = $stmt->fetchAll();

    foreach($mensajes as $msg){
        $clase = ($msg['IDemisor'] == $usuarioID) ? 'der' : 'izq'; // Change classes to match CSS
        echo "<div class='mensaje $clase'>".htmlspecialchars($msg['Mensaje'])."</div>";
    }
}
 #endregion
        break;


    case 'RecibirNuevoChat':
 #region RecibirNuevoChat
 $miID = $_SESSION['usuario'];

$stmt = $pdo->prepare("SELECT DISTINCT u.IDusuario, u.NombreUsuario, u.Foto_perfil
    FROM Mensajes AS m
    JOIN Usuario AS u 
      ON u.IDusuario = m.IDemisor 
      OR u.IDusuario = m.IDreceptor
    WHERE u.IDusuario != ? 
      AND (m.IDemisor = ? OR m.IDreceptor = ?)
");
$stmt->execute([$miID, $miID, $miID]);


while($row = $stmt->fetch()){
    $img = empty($row['Foto_perfil']) ? 'https://i.pravatar.cc/90?img=1' : $row['Foto_perfil'];
    echo "
    <div class='contacto' data-id='{$row['IDusuario']}' data-nombre='{$row['NombreUsuario']}' data-img='{$img}'>
        <img src='{$img}' alt='{$row['NombreUsuario']}' data-img='{$img}'>
        <span>{$row['NombreUsuario']}</span>
    </div>
    ";
}
 #endregion

        break;


    case 'RecibirUsuarios':
 #region RecibirUsuarios
 $IDreceptor = $_POST['IDreceptor'] ?? null;

// Obtener todos los usuarios excepto uno mismo
$stmt = $pdo->prepare("SELECT * FROM Mensajes
        WHERE (IDemisor = ? AND IDreceptor = ?)
           OR (IDemisor = ? AND IDreceptor = ?)");
$stmt->execute([$IDreceptor,$_SESSION['usuario'] ,$_SESSION['usuario'],$IDreceptor]);

if($stmt->rowCount() > 0){
    // Ya existe un chat, no mostrar al usuario
    header("Location: " .(MAIN_URL."chat/chat.php"));
}else{
$stmt = $pdo->prepare("SELECT IDusuario, NombreUsuario , Foto_perfil 
    FROM Usuario 
    WHERE IDusuario = ? and IDusuario != ?
    ORDER BY NombreUsuario");
$stmt->execute([$IDreceptor, $_SESSION['usuario']]);

if($row = $stmt->fetch(PDO::FETCH_ASSOC)){

    $img = empty($row['Foto_perfil']) ? 'https://i.pravatar.cc/90?img=1' : $row['Foto_perfil'];
    echo "
    <div class='contacto' data-id='{$row['IDusuario']}' data-nombre='{$row['NombreUsuario']}' data-img='{$img}'>
        <img src='{$img}' alt='{$row['NombreUsuario']}'>
        <span>{$row['NombreUsuario']}</span>
    </div>
    ";


} 
} 
 #endregion

        break;
    default:
        // Acción por defecto o error
        break;
}