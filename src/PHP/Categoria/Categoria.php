<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__.'/../Conexion.php';

if (isset($_POST['CategoriaEliminar'])) {
        $id = $_POST['id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM Categoria WHERE IDcategoria = ?");
        $stmt->execute([$id]);
        header("Location: " . '/perfil/perfil.php');
    } else {
        echo "ID no válido";
    }

}

if (isset($_POST['CategoriaCrear'])) {
    $Nombre = $_POST['Nombre'] ?? null;
    header('Content-Type: application/json');
    require_once __DIR__."/../Servicios/consultas-Servicios.php";

    if ($Nombre) {

        $srv = new Servicios($pdo);
        $Rcat = $srv->Categorias();

        if ($Rcat) {
            foreach ($Rcat as $categoria) {
                if (strtolower($categoria['Nombre_Categoria']) === strtolower($Nombre)) {
                    echo json_encode([
                        "success" => false,
                        "message" => "La categoría ya existe"
                    ]);
                    exit();
                }
            }
        }
        $stmt = $pdo->prepare("INSERT INTO Categoria (Nombre_Categoria,Fecha_Categoria) VALUES (?, NOW())");
        $stmt->execute([$Nombre]);

        $id = $pdo->lastInsertId();
        echo json_encode([
            "success" => true,
            "id" => $id,
            "nombre" => $Nombre
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Nombre no válido"
        ]);
    }
    exit();
}

if (isset($_POST['CategoriaEditar'])) {
    $id = $_POST['id'] ?? null;
    $Nombre = $_POST['Nombre'] ?? null;
    header('Content-Type: application/json');
    if ($id && $Nombre) {
        // Verificar si ya existe una categoría con ese nombre (opcional)
        require_once __DIR__."/../Servicios/consultas-Servicios.php";
        $srv = new Servicios($pdo);
        $Rcat = $srv->CatRepetida($Nombre, $id);

        if ($Rcat->fetch()) {
            echo json_encode([
                "success" => false,
                "message" => "Ya existe una categoría con ese nombre"
            ]);
            exit();
        }
        $stmt = $pdo->prepare("UPDATE Categoria SET Nombre_Categoria = ? WHERE IDcategoria = ?");
        $stmt->execute([$Nombre, $id]);
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "success" => true,
                "id" => $id,
                "nombre" => $Nombre
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No se pudo editar la categoría"
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Datos inválidos"
        ]);
    }
    exit();
}

}


if ($ServiciosMostrar === "Categorias") {
    require_once __DIR__.'/../Conexion.php';
    require_once __DIR__."/../Servicios/consultas-Servicios.php";

    $srv = new Servicios($pdo);
    
    $categorias = $srv->Categorias();

    if ($categorias) {

        foreach ($categorias as $categoria) {
            ?>
            <div class="buscador-admin">
            <div 
            class="categoria-item" 
            data-idcat="<?= $categoria['IDcategoria'] ?>"
            data-nombre="<?= htmlspecialchars($categoria['Nombre_Categoria']) ?>"
            >
                <input data-idcat="<?= $categoria['IDcategoria'] ?>" type="text" name="NombreCat" value="<?= htmlspecialchars($categoria['Nombre_Categoria']) ?>"></input >

                <button data-idcat="<?= $categoria['IDcategoria'] ?>" class="btn-accion updcat">Guardar</button>
                <button data-idcat="<?= $categoria['IDcategoria'] ?>" class="btn-peligro delcat">X</button>
            </div>
            </div>
            <?php
        }
    }
}
