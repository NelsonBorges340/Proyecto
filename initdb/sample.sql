CREATE DATABASE IF NOT EXISTS hirenear;
USE hirenear;

CREATE TABLE IF NOT EXISTS Usuario ( 
    IDusuario INT PRIMARY KEY AUTO_INCREMENT,
    NombreUsuario VARCHAR(50) NOT NULL,
    CI VARCHAR(15)  ,
    Foto_perfil blob ,
    Correo VARCHAR(100) NOT NULL,
    Contraseña VARCHAR(80) NOT NULL,
    Descripcion VARCHAR(255) ,
    Telefono VARCHAR(15) ,
    Tipo_usuario ENUM('cliente', 'vendedor', 'admin') NOT NULL CHECK (Tipo_usuario IN ('cliente', 'vendedor', 'admin')),
    Departamento ENUM(
        'Artigas', 'Canelones', 'Cerro Largo', 'Colonia', 'Durazno',
        'Flores', 'Florida', 'Lavalleja', 'Maldonado', 'Montevideo',
        'Paysandú', 'Río Negro', 'Rivera', 'Rocha', 'Salto',
        'San José', 'Soriano', 'Tacuarembó', 'Treinta y Tres'
    ) NOT NULL
);
/**elimine metodo de pago porque no tiene sentido ponerlo ya**/


CREATE TABLE IF NOT EXISTS Categoria (
    IDcategoria INT PRIMARY KEY AUTO_INCREMENT,
    Nombre_Categoria VARCHAR(50) NOT NULL CHECK (TRIM(Nombre_Categoria) <> ''),
    Fecha_Categoria DATE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS Servicio ( 
    IDservicio INT PRIMARY KEY AUTO_INCREMENT,
    Fecha_servicio DATE ,
    Calificacion INT CHECK (Calificacion BETWEEN 1 AND 5 OR Calificacion IS NULL),
    Descripcion TEXT,
    Precio DECIMAL(10,2) NOT NULL CHECK (Precio > 0),
    Nombre_Servicio VARCHAR(100) NOT NULL CHECK (TRIM(Nombre_Servicio) <> ''),
    Imagen_Servicio BLOB,
    IDcategoria INT NOT NULL,
    IDusuario INT NOT NULL,
    Ubicacion VARCHAR(100) NOT NULL,
    FOREIGN KEY (IDcategoria) REFERENCES Categoria(IDcategoria) ON DELETE CASCADE,
    FOREIGN KEY (IDusuario) REFERENCES Usuario(IDusuario) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Horario (
    IDhorario INT PRIMARY KEY AUTO_INCREMENT,
    DiaSemana ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo') NOT NULL,
    HoraInicio TIME NOT NULL,
    HoraFin TIME NOT NULL,
    CHECK (HoraInicio < HoraFin)
);

 CREATE TABLE IF NOT EXISTS ServicioHorario (
    IDservicio INT,
    IDhorario INT,
    PRIMARY KEY (IDservicio, IDhorario),
    FOREIGN KEY (IDservicio) REFERENCES Servicio(IDservicio) ON DELETE CASCADE,
    FOREIGN KEY (IDhorario) REFERENCES Horario(IDhorario) ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS Imagenes_servicio (
    IDimagen INT PRIMARY KEY AUTO_INCREMENT,
    IDservicio INT,
    Ruta_imagen VARCHAR(255),
    FOREIGN KEY (IDservicio) REFERENCES Servicio(IDservicio)
);




/**fue añadido calificacion**/
CREATE TABLE IF NOT EXISTS Comentario (
    IDcomentario INT PRIMARY KEY AUTO_INCREMENT,
    Fecha_comentario DATE NOT NULL,
    Contenido varchar(255) NOT NULL CHECK (TRIM(Contenido) <> ''),
    IDemisor INT NOT NULL,
    IDreceptor INT NOT NULL,
    Titulo varchar(50) NOT NULL,
    Calificacion INT CHECK (Calificacion BETWEEN 1 AND 5),
    FOREIGN KEY (IDemisor) REFERENCES Usuario(IDusuario) ON DELETE CASCADE,
    FOREIGN KEY (IDreceptor) REFERENCES Usuario(IDusuario) ON DELETE CASCADE
);

/**comentario debilidad de servicio porque si servicio no existe comentario tampoco**/
CREATE TABLE IF NOT EXISTS ComentarioServicio (
    IDcomentario INT,
    IDservicio INT,
    PRIMARY KEY (IDcomentario, IDservicio),
    FOREIGN KEY (IDcomentario) REFERENCES Comentario(IDcomentario) ON DELETE CASCADE,
    FOREIGN KEY (IDservicio) REFERENCES Servicio(IDservicio) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Mensajes (
    IDmensaje INT AUTO_INCREMENT PRIMARY KEY,
    IDemisor INT NOT NULL,
    IDreceptor INT NOT NULL,
    Mensaje TEXT NOT NULL,
    Fecha_envio DATETIME NOT NULL,
FOREIGN KEY (IDemisor) REFERENCES Usuario(IDusuario) ON DELETE CASCADE,
FOREIGN KEY (IDreceptor) REFERENCES Usuario(IDusuario) ON DELETE CASCADE
);
/**añadi esta para cumplir con los objetivos pedidos**/

CREATE TABLE IF NOT EXISTS Notificaciones (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    IDcliente INT NOT NULL,
    IDvendedor INT NOT NULL,        
    Tipo VARCHAR(50),               
    Mensaje TEXT,                  
    Leido BOOLEAN DEFAULT FALSE,
    Fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (IDcliente) REFERENCES Usuario(IDusuario) ON DELETE CASCADE,
    FOREIGN KEY (IDvendedor) REFERENCES Usuario(IDusuario) ON DELETE CASCADE
);

/**agrege algunas cosas a esto como los bools y los ultimos datatime**/

CREATE TABLE IF NOT EXISTS HistorialCompra (
    IDhistorial INT PRIMARY KEY AUTO_INCREMENT,
    IDusuario INT NOT NULL,
    IDservicio INT NOT NULL,
    FechaCompra DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Terminado BOOLEAN DEFAULT FALSE,
    Confirmacion BOOLEAN DEFAULT FALSE,
    FechaInicio DATETIME,
    FechaFin DATETIME,
    FOREIGN KEY (IDusuario) REFERENCES Usuario(IDusuario) ON DELETE CASCADE,
    FOREIGN KEY (IDservicio) REFERENCES Servicio(IDservicio) ON DELETE CASCADE
);

insert into Categoria (Nombre_Categoria) values ('Jardineria');

DELIMITER $$

CREATE TRIGGER  check_fecha_categoria
BEFORE INSERT ON Categoria
FOR EACH ROW
BEGIN
    IF NEW.Fecha_Categoria > CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Fecha_Categoria no puede ser mayor a la fecha actual';
    END IF;
END$$

CREATE TRIGGER check_fecha_categoria_update
BEFORE UPDATE ON Categoria
FOR EACH ROW
BEGIN
    IF NEW.Fecha_Categoria > CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Fecha_Categoria no puede ser mayor a la fecha actual';
    END IF;
END$$

CREATE TRIGGER check_fecha_servicio
BEFORE INSERT ON Servicio
FOR EACH ROW
BEGIN
    IF NEW.Fecha_servicio > CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Fecha_servicio no puede ser mayor a la fecha actual';
    END IF;
END$$

CREATE TRIGGER check_fecha_servicio_update
BEFORE UPDATE ON Servicio
FOR EACH ROW
BEGIN
    IF NEW.Fecha_servicio > CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Fecha_servicio no puede ser mayor a la fecha actual';
    END IF;
END$$

CREATE TRIGGER check_fecha_compra
BEFORE INSERT ON HistorialCompra
FOR EACH ROW
BEGIN
    IF NEW.FechaCompra > CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'FechaCompra no puede ser mayor a la fecha actual';
    END IF;
END$$

CREATE TRIGGER check_fecha_compra_update
BEFORE UPDATE ON HistorialCompra
FOR EACH ROW
BEGIN
    IF NEW.FechaCompra > CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'FechaCompra no puede ser mayor a la fecha actual';
    END IF;
END$$




DELIMITER ;
