<?php

// Conexión PDO ya incluida
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once (__DIR__."/../conexion.php");

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
    echo "
    <div class='contacto' data-id='{$row['IDusuario']}' data-nombre='{$row['NombreUsuario']}' data-img='{$row['Foto_perfil']}'>
        <img src='{$row['Foto_perfil']}' alt='{$row['NombreUsuario']}' data-img='{$row['Foto_perfil']}'>
        <span>{$row['NombreUsuario']}</span>
    </div>
    ";
}

?>