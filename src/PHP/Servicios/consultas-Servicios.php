<?php
require_once __DIR__.'/../Conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    
    session_start();
}



class Servicios {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function buscarPorIDusr() {
        $stmt = $this->pdo->prepare("SELECT IDservicio, Nombre_Servicio, Ubicacion, Precio, Descripcion FROM Servicio where IDusuario = ?");
        $stmt->execute([$_SESSION['usuario']]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // devuelve fila o false
    }

    public function Buscar() {
        $stmt = $this->pdo->query("SELECT IDservicio, Nombre_Servicio, Ubicacion, Precio, Descripcion, IDcategoria, IDusuario FROM Servicio");
        return $stmt; // devuelve fila o false
    }

    public function buscarFiltro($categoria = null, $ubicacion = null, $min = 0, $max = 999999) {
        $sql = "SELECT * FROM servicio WHERE 1=1";
        $params = [];
        
        if ($categoria && $categoria !== "todos") {
            $sql .= " AND IDcategoria = ?";
            $params[] = $categoria;
        }
        if ($ubicacion && $ubicacion !== "todos") {
            $sql .= " AND Ubicacion = :ubi";
            $params[":ubi"] = $ubicacion;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }



        public function buscarPorID($ID) {
            $stmt = $this->pdo->prepare("SELECT IDservicio, Nombre_Servicio, Ubicacion, Precio, Descripcion, IDcategoria, IDusuario FROM Servicio where IDservicio = ?");
            $stmt->execute([$ID]);
            return $stmt->fetch(PDO::FETCH_ASSOC); // devuelve fila o false
        }

    public function BuscarCategoria($IDcategoria) {
        $stmt = $this->pdo->prepare("SELECT IDcategoria, Nombre_Categoria, Fecha_Categoria FROM Categoria where IDcategoria = ?");
        $stmt->execute([$IDcategoria]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // devuelve fila o false
    }

    public function Categorias() {
    $stmt = $this->pdo->prepare("SELECT IDcategoria, Nombre_Categoria, Fecha_Categoria FROM Categoria");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC); // devuelve todas las filas
}


    public function actualizarServicio($id, $nombre, $descripcion, $categoria, $ubicacion, $precio) {
        $stmt = $this->pdo->prepare("UPDATE servicio SET Nombre_Servicio = ?, Descripcion = ?, IDcategoria = ?, Ubicacion = ?, Precio = ? WHERE IDservicio = ? AND IDusuario = ?");
        return $stmt->execute([$nombre, $descripcion, $categoria, $ubicacion, $precio, $id,$_SESSION['usuario']]);
    }
   
public function CatRepetida( $Nombre, $id) {
    $stmt = $this->pdo->prepare("SELECT IDcategoria FROM Categoria WHERE LOWER(Nombre_Categoria) = LOWER(?) AND IDcategoria != ?");
    $stmt->execute([$Nombre, $id]);
    return $stmt;

}

public function Notificacion($IDusuario) {
    $stmt = $this->pdo->prepare("SELECT ID,IDcliente,IDvendedor,Tipo,Mensaje,Leido,Fecha FROM Notificaciones WHERE IDvendedor = ?");
    $stmt->execute([$IDusuario]);
    return $stmt;

}

}

?>
