-- 1. Elimina la base de datos por completo si es que existe
DROP DATABASE IF EXISTS gestion_practicas;

-- 2. La vuelve a crear desde cero, totalmente vacía
CREATE DATABASE gestion_practicas;

-- 3. Le dice a MariaDB que use esta nueva base de datos vacía
USE gestion_practicas;

-- 1. Tabla: universidades
CREATE TABLE universidades (
    id_universidad INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- 2. Tabla: sedes
CREATE TABLE sedes (
    id_sede INT AUTO_INCREMENT PRIMARY KEY,
    id_university INT NOT NULL, -- Nota: en tu diagrama dice fk_universidad, lo mapeamos al id_universidad
    ubicacion VARCHAR(255),
    FOREIGN KEY (id_university) REFERENCES universidades(id_universidad) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 3. Tabla: directores
CREATE TABLE directores (
    id_director INT AUTO_INCREMENT PRIMARY KEY,
    apellidos VARCHAR(255) NOT NULL,
    nombres VARCHAR(255) NOT NULL,
    correo VARCHAR(255),
    telefono VARCHAR(50)
) ENGINE=InnoDB;

-- 4. Tabla: carreras
CREATE TABLE carreras (
    id_career INT AUTO_INCREMENT PRIMARY KEY, -- En tu diagrama aparece id_carrera
    nombre VARCHAR(255) NOT NULL,
    id_sede INT NOT NULL,
    id_director INT NOT NULL,
    FOREIGN KEY (id_sede) REFERENCES sedes(id_sede) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_director) REFERENCES directores(id_director) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 5. Tabla: estudiantes
CREATE TABLE estudiantes (
    id_estudiante INT AUTO_INCREMENT PRIMARY KEY,
    rut INT NOT NULL, -- Nota: Si usas el RUT chileno con guión y dígito verificador (ej: 12345678-K), te recomiendo cambiarlo a VARCHAR(12)
    apellidos VARCHAR(255) NOT NULL,
    nombres VARCHAR(255) NOT NULL,
    id_carrera INT NOT NULL,
    correo VARCHAR(255),
    FOREIGN KEY (id_carrera) REFERENCES carreras(id_career) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 6. Tabla: encargados
CREATE TABLE encargados (
    id_encargado INT AUTO_INCREMENT PRIMARY KEY,
    id_carrera INT NOT NULL,
    apellidos VARCHAR(255) NOT NULL,
    nombres VARCHAR(255) NOT NULL,
    correo VARCHAR(255),
    telefono VARCHAR(50),
    FOREIGN KEY (id_carrera) REFERENCES carreras(id_career) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 7. Tabla: empresa
CREATE TABLE empresa (
    id_empresa INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    ubicacion VARCHAR(255),
    telefono VARCHAR(255),
    correo VARCHAR(255)
) ENGINE=InnoDB;

-- 8. Tabla: tutores
CREATE TABLE tutores (
    id_tutor INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    apellidos VARCHAR(255) NOT NULL,
    nombres VARCHAR(255) NOT NULL,
    cargo VARCHAR(255),
    correo VARCHAR(255),
    telefono VARCHAR(255),
    FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 9. Tabla: estados
CREATE TABLE estados (
    id_estado INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- 10. Tabla: documentos
CREATE TABLE documentos (
    id_documentos INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255),
    tipo VARCHAR(255)
) ENGINE=InnoDB;

-- 11. Tabla Central: practicas
CREATE TABLE practicas (
    id_practica INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT NOT NULL,
    id_encargado INT NOT NULL,
    id_tutor INT NOT NULL,
    fecha_inicio_oferta DATE,
    fecha_termino_oferta DATE,
    fecha_inicio_practica DATE,
    fecha_termino_practica DATE,
    id_estado INT NOT NULL,
    id_documentos INT NOT NULL,
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id_estudiante) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_encargado) REFERENCES encargados(id_encargado) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_tutor) REFERENCES tutores(id_tutor) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_estado) REFERENCES estados(id_estado) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_documentos) REFERENCES documentos(id_documentos) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Insertar estados obligatorios para las prácticas
INSERT INTO estados (nombre) VALUES ('Pendiente'), ('Aceptada'), ('En Curso'), ('Finalizada'), ('Rechazada');

-- Insertar una universidad y una sede de prueba
INSERT INTO universidades (nombre) VALUES ('Universidad de Ejemplo');
INSERT INTO sedes (id_university, ubicacion) VALUES (1, 'Campus Central');

-- Insertar una carrera de prueba (Requiere un director primero)
INSERT INTO directores (apellidos, nombres, correo, telefono) VALUES ('Gomez', 'Juan', 'juan.gomez@u.cl', '123456');
INSERT INTO carreras (nombre, id_sede, id_director) VALUES ('Ingeniería en Informática', 1, 1);