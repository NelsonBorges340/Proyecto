<?php
echo "";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once (__DIR__."/../conexion.php");

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

    echo "
    <div class='contacto' data-id='{$row['IDusuario']}' data-nombre='{$row['NombreUsuario']}' data-img='{$row['Foto_perfil']}'>
        <img src='" . (empty($row['Foto_perfil']) ? '../public/img/default-profile.png' : $row['Foto_perfil']) . "' alt='{$row['NombreUsuario']}'>
        <span>{$row['NombreUsuario']}</span>
    </div>
    ";


} 
} 
