<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $conn->begin_transaction();
        
        $nombreCompleto = $_POST['usuario'];
        $email = $_POST['correo'];
        $contrasena = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $rol = 'cliente'; // Puedes cambiar esto si permites elegir rol
        
        // Procesar imagen si se proporciona
        $imagen_perfil = null;
        $tipo_mime = null;
        
        if (isset($_FILES['imagen_perfil']) && $_FILES['imagen_perfil']['error'] === 0) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif'];
            $tipo_mime = mime_content_type($_FILES['imagen_perfil']['tmp_name']);
            
            if (!in_array($tipo_mime, $allowed)) {
                throw new Exception('Tipo de archivo no permitido');
            }
            
            $imagen_perfil = file_get_contents($_FILES['imagen_perfil']['tmp_name']);
            if ($imagen_perfil === false) {
                throw new Exception('Error al leer la imagen');
            }
        }

        if ($imagen_perfil !== null) {
            $sql = "INSERT INTO Usuario (nombreCompleto, email, contrasena, rol, imagen_perfil, tipo_mime) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $nombreCompleto, $email, $contrasena, $rol, $imagen_perfil, $tipo_mime);
        } else {
            $sql = "INSERT INTO Usuario (nombreCompleto, email, contrasena, rol) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $nombreCompleto, $email, $contrasena, $rol);
        }

        if ($stmt->execute()) {
            $usuario_id = $conn->insert_id;
            
            // Si es cliente, crear entrada en tabla Cliente
            if ($rol === 'cliente') {
                $sql = "INSERT INTO Cliente (idCliente) VALUES (?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $usuario_id);
                $stmt->execute();
            }
            
            $conn->commit();
            header("Location: ../html/finicio.html?registro=ok");
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}
?>

