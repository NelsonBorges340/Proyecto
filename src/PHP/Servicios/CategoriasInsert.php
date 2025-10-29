<?php

require_once __DIR__ . '/../Conexion.php';
try {
if ($modo === "insert") {

    // Consulta directa sin usar clase
    $stmt = $pdo->prepare("SELECT IDcategoria, Nombre_Categoria FROM Categoria ORDER BY Nombre_Categoria");
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $id = htmlspecialchars($row['IDcategoria']);
    $nombre = htmlspecialchars($row['Nombre_Categoria']);
    echo "<option value=\"$id\">$nombre</option>";
}

} elseif ($modo === "update") {
    

    // Consulta directa sin usar clase
    $stmt = $pdo->prepare("SELECT IDcategoria, Nombre_Categoria FROM Categoria ORDER BY Nombre_Categoria");
    $stmt->execute();
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    echo "<label class='categoria'><input type='checkbox' value='{$row['IDcategoria']}'>{$row['Nombre_Categoria']}</label>";
    }

} elseif ($modo === "filtro") {

}

} catch (PDOException $e) {
    error_log("Error en CategoriasInsert: " . $e->getMessage());
    echo "<option value=''>Error al cargar categorías</option>";
}


?>