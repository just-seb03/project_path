DROP TABLE IF EXISTS Practicas;
DROP TABLE IF EXISTS Encargados;
DROP TABLE IF EXISTS Estudiantes;
DROP TABLE IF EXISTS Tutores;
DROP TABLE IF EXISTS Carreras;
DROP TABLE IF EXISTS Sedes;
DROP TABLE IF EXISTS Empresa;
DROP TABLE IF EXISTS Estados;
DROP TABLE IF EXISTS Directores;
DROP TABLE IF EXISTS Universidades;

CREATE TABLE Universidades (
    id_universidad INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE Directores (
    id_director INT PRIMARY KEY AUTO_INCREMENT,
    apellidos VARCHAR(50) NOT NULL,
    nombres VARCHAR(50) NOT NULL,
    correo VARCHAR(100) UNIQUE,
    telefono VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    cod_invitacion VARCHAR(50) UNIQUE
);

CREATE TABLE Estados (
    id_estado INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL
);

CREATE TABLE Empresa (
    id_empresa INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    ubicacion VARCHAR(100),
    contacto VARCHAR(50)
);

CREATE TABLE Sedes (
    id_sede INT PRIMARY KEY AUTO_INCREMENT,
    id_universidad INT,
    ubicacion VARCHAR(50),
    CONSTRAINT fk_id_universidad FOREIGN KEY (id_universidad) 
        REFERENCES Universidades(id_universidad) ON DELETE CASCADE
);

CREATE TABLE Tutores (
    id_tutor INT PRIMARY KEY AUTO_INCREMENT,
    id_empresa INT,
    apellidos VARCHAR(50) NOT NULL,
    nombres VARCHAR(50) NOT NULL,
    cargo VARCHAR(50),
    correo VARCHAR(100) UNIQUE,
    telefono VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    CONSTRAINT fk_id_empresa FOREIGN KEY (id_empresa) 
        REFERENCES Empresa(id_empresa) ON DELETE SET NULL
);

CREATE TABLE Carreras (
    id_carrera INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    id_sede INT,
    id_director INT,
    CONSTRAINT fk_sedes FOREIGN KEY (id_sede) 
        REFERENCES Sedes(id_sede),
    CONSTRAINT fk_id_director FOREIGN KEY (id_director) 
        REFERENCES Directores(id_director)
);

CREATE TABLE Estudiantes (
    id_estudiante INT PRIMARY KEY AUTO_INCREMENT,
    rut INT UNIQUE, 
    apellidos VARCHAR(50) NOT NULL,
    nombres VARCHAR(50) NOT NULL,
    id_carrera INT,
    correo VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL,
    CONSTRAINT fk_carreras FOREIGN KEY (id_carrera) 
        REFERENCES Carreras(id_carrera)
);

CREATE TABLE Encargados (
    id_encargado INT PRIMARY KEY AUTO_INCREMENT,
    id_carrera INT,
    apellidos VARCHAR(50) NOT NULL,
    nombres VARCHAR(50) NOT NULL,
    correo VARCHAR(100) UNIQUE,
    telefono VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    cod_invitacion VARCHAR(50) UNIQUE,
    CONSTRAINT fk_id_carreras FOREIGN KEY (id_carrera) 
        REFERENCES Carreras(id_carrera)
);

CREATE TABLE Practicas (
    id_practica INT PRIMARY KEY AUTO_INCREMENT,
    id_estudiante INT,
    id_encargado INT,
    id_tutor INT,
    fecha_inicio DATE NOT NULL,
    fecha_termino DATE,
    id_Estado INT,
    CONSTRAINT fk_id_estudiante FOREIGN KEY (id_estudiante) 
        REFERENCES Estudiantes(id_estudiante),
    CONSTRAINT fk_id_encargado FOREIGN KEY (id_encargado) 
        REFERENCES Encargados(id_encargado),
    CONSTRAINT fk_id_tutor FOREIGN KEY (id_tutor) 
        REFERENCES Tutores(id_tutor),
    CONSTRAINT fk_id_estados FOREIGN KEY (id_Estado) 
        REFERENCES Estados(id_estado)
);

CREATE TABLE recuperacion_pass (
    id INT AUTO_INCREMENT PRIMARY KEY,
    correo VARCHAR(150) NOT NULL,
    codigo VARCHAR(6) NOT NULL,
    expiracion DATETIME NOT NULL
);