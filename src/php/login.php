<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    $sql = "SELECT idUsuario, contrasena, rol, nombreCompleto FROM Usuario WHERE nombreCompleto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hash, $rol, $nombreCompleto);
        $stmt->fetch();
        if (password_verify($password, $hash)) {
            $_SESSION['usuario_id'] = $id;
            $_SESSION['usuario_rol'] = $rol;
            $_SESSION['nombreCompleto'] = $nombreCompleto;
            if ($rol == 'cliente' || $rol == 'proveedor') {
                header("Location: ../html/index.php");
            } elseif ($rol == 'administrador') {
                header("Location: ../html/admin-panel.html");
            }
            exit;
        } else {
            // Contraseña incorrecta
            echo "<script>alert('Contraseña incorrecta');window.location='../html/finicio.html';</script>";
        }
    } else {
        echo "<script>
            alert('Usuario no registrado. Debe registrarse.');
            window.location='../html/registro.html';
        </script>";
    }
    $stmt->close();
}
?>