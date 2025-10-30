<?php
// Conexión a la base de datos (para entorno Proxmox con Docker)
$host = "db";              // nombre del servicio del contenedor de base de datos
$dbname = "proyecto_its";  // nombre de la base de datos
$username = "user";        // usuario configurado en .env o docker-compose
$password = "inoxfile22";    // contraseña configurada en .env o docker-compose

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,      // errores como excepciones
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // resultados como arrays asociativos
            PDO::ATTR_PERSISTENT => false                     // sin conexión persistente
        ]
    );
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>