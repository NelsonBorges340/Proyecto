<?php

require __DIR__.'/../Conexion.php';

class Usuario {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function buscarPorID($IDusuario) {
        $stmt = $this->pdo->prepare("SELECT IDusuario, Contraseña, NombreUsuario, Tipo_usuario, Correo, Descripcion, Foto_perfil FROM Usuario WHERE IDusuario = ?");
        $stmt->execute([$IDusuario]);

        
        return $stmt->fetch(PDO::FETCH_ASSOC); // devuelve fila o false
    }

    public function buscarPorCorreo($Correo) {
        $stmt = $this->pdo->prepare("SELECT IDusuario, Contraseña, NombreUsuario, Tipo_usuario, Correo, Descripcion FROM Usuario WHERE Correo = ?");
        $stmt->execute([$Correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // devuelve fila o false
    }

    public function Usuarios() {
            $stmt = $this->pdo->prepare("SELECT IDusuario, NombreUsuario, Correo,Tipo_usuario, Foto_perfil, Descripcion, Telefono, Departamento, CI FROM Usuario");
            $stmt->execute();
            return $stmt; // devuelve fila o false
    }


}

?>
