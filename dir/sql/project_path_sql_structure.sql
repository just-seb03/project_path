DROP DATABASE IF EXISTS path_db;
CREATE DATABASE path_db;
USE path_db;

CREATE TABLE universidades (
    id_universidad INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE sedes (
    id_sede INT AUTO_INCREMENT PRIMARY KEY,
    id_university INT NOT NULL,
    ubicacion VARCHAR(255),
    FOREIGN KEY (id_university) REFERENCES universidades(id_universidad) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE directores (
    id_director INT AUTO_INCREMENT PRIMARY KEY,
    apellidos VARCHAR(255) NOT NULL,
    nombres VARCHAR(255) NOT NULL,
    correo VARCHAR(255) UNIQUE,
    telefono VARCHAR(50),
    password VARCHAR(255) NOT NULL,
    cod_invitacion VARCHAR(50) UNIQUE
) ENGINE=InnoDB;

CREATE TABLE carreras (
    id_career INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    id_sede INT NOT NULL,
    id_director INT NOT NULL,
    FOREIGN KEY (id_sede) REFERENCES sedes(id_sede) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_director) REFERENCES directores(id_director) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE estudiantes (
    id_estudiante INT AUTO_INCREMENT PRIMARY KEY,
    rut INT NOT NULL,
    apellidos VARCHAR(255) NOT NULL,
    nombres VARCHAR(255) NOT NULL,
    id_carrera INT NOT NULL,
    correo VARCHAR(255) UNIQUE,
    password VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_carrera) REFERENCES carreras(id_career) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE encargados (
    id_encargado INT AUTO_INCREMENT PRIMARY KEY,
    id_carrera INT NOT NULL,
    apellidos VARCHAR(255) NOT NULL,
    nombres VARCHAR(255) NOT NULL,
    correo VARCHAR(255) UNIQUE,
    telefono VARCHAR(50),
    password VARCHAR(255) NOT NULL,
    cod_invitacion VARCHAR(50) UNIQUE,
    FOREIGN KEY (id_carrera) REFERENCES carreras(id_career) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE empresa (
    id_empresa INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    ubicacion VARCHAR(255),
    telefono VARCHAR(255),
    correo VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE tutores (
    id_tutor INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    apellidos VARCHAR(255) NOT NULL,
    nombres VARCHAR(255) NOT NULL,
    cargo VARCHAR(255),
    correo VARCHAR(255) UNIQUE,
    telefono VARCHAR(255),
    password VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE estados (
    id_estado INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- `documentos` cumple doble función:
-- 1) Catálogo original (filas con id_practica = NULL), referenciado
--    de forma fija y obligatoria por practicas.id_documentos.
-- 2) Historial real de informes subidos por el estudiante (filas con
--    id_practica seteado), pueden ser muchas por práctica.
CREATE TABLE documentos (
    id_documentos INT AUTO_INCREMENT PRIMARY KEY,
    id_practica INT NULL,
    titulo VARCHAR(255),
    tipo VARCHAR(255),
    tipo_informe ENUM('avance', 'final', 'autoevaluacion', 'bitacora') NULL,
    nombre_archivo VARCHAR(255) NULL,
    ruta_archivo VARCHAR(500) NULL,
    comentario VARCHAR(500) NULL,
    comentario_revisor VARCHAR(500) NULL,
    estado ENUM('pendiente', 'aprobado', 'rechazado') NOT NULL DEFAULT 'pendiente',
    fecha_subida DATETIME NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

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

-- documentos.id_practica se agrega como FK después de crear `practicas`,
-- porque `documentos` se crea antes (practicas depende de documentos).
ALTER TABLE documentos
    ADD CONSTRAINT fk_documentos_practica FOREIGN KEY (id_practica)
        REFERENCES practicas(id_practica) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE mensajes (
    id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
    id_practica INT NOT NULL,
    emisor_rol ENUM('estudiante', 'tutor', 'encargado') NOT NULL,
    emisor_correo VARCHAR(255) NOT NULL,
    destinatario_rol ENUM('tutor', 'encargado') NOT NULL,
    contenido TEXT NOT NULL,
    fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_practica) REFERENCES practicas(id_practica) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Datos de prueba. Todas las contraseñas son: 123456
-- Guardadas con password_hash() (bcrypt), compatibles con
-- password_verify() del login.php actual.
-- ============================================================

INSERT INTO universidades (nombre) VALUES
('Universidad de Santiago'), ('Universidad de Chile'), ('Universidad Católica'),
('Universidad de Concepción'), ('Universidad de Valparaíso'), ('Universidad del Bio-Bío'),
('Universidad de La Serena'), ('Universidad de Antofagasta'), ('Universidad de Talca');

INSERT INTO sedes (id_university, ubicacion) VALUES
(2, 'Campus Sur'), (2, 'Campus Norte'), (3, 'Casa Central Santiago'),
(4, 'Campus Concepción'), (5, 'Sede Reñaca'), (6, 'Campus Concepción'),
(7, 'Campus Andrés Bello'), (8, 'Campus Coloso'), (9, 'Campus Talca');

INSERT INTO directores (apellidos, nombres, correo, telefono, password, cod_invitacion) VALUES
('Alvarez', 'María', 'maria.alvarez@u.cl', '+56911112222', '123456', 'DIR-AAA001'),
('Soto', 'Carlos', 'carlos.soto@u.cl', '+56922223333', '123456', 'DIR-AAA002'),
('Muñoz', 'Ana', 'ana.munoz@u.cl', '+56933334444', '123456', 'DIR-AAA003'),
('Contreras', 'Pedro', 'pedro.contreras@u.cl', '+56944445555', '123456', 'DIR-AAA004'),
('Morales', 'Luisa', 'luisa.morales@u.cl', '+56955556666', '123456', 'DIR-AAA005'),
('Rojas', 'Jorge', 'jorge.rojas@u.cl', '+56966667777', '123456', 'DIR-AAA006'),
('Silva', 'Elena', 'elena.silva@u.cl', '+56977778888', '123456', 'DIR-AAA007'),
('Castro', 'Ricardo', 'ricardo.castro@u.cl', '+56988889999', '123456', 'DIR-AAA008'),
('López', 'Sofía', 'sofia.lopez@u.cl', '+56999990000', '123456', 'DIR-AAA009');

INSERT INTO carreras (nombre, id_sede, id_director) VALUES
('Ingeniería Civil Industrial', 1, 1), ('Ingeniería Comercial', 2, 2), ('Medicina', 3, 3),
('Derecho', 4, 4), ('Psicología', 5, 5), ('Arquitectura', 6, 6),
('Enfermería', 7, 7), ('Diseño Gráfico', 8, 8), ('Kinesiología', 9, 9);

INSERT INTO estudiantes (rut, apellidos, nombres, id_carrera, correo, password) VALUES
(18234567, 'González', 'Sebastián', 1, 'sebastian.gonzalez@correo.cl', '123456'),
(19456123, 'Rodríguez', 'Camila', 1, 'camila.rodriguez@correo.cl', '123456'),
(20112345, 'Muñoz', 'Benjamín', 2, 'benjamin.munoz@correo.cl', '123456'),
(17987654, 'Rojas', 'Valentina', 3, 'valentina.rojas@correo.cl', '123456'),
(19555444, 'Díaz', 'Nicolás', 4, 'nicolas.diaz@correo.cl', '123456'),
(20333222, 'Pérez', 'Catalina', 5, 'catalina.perez@correo.cl', '123456'),
(18777888, 'Soto', 'Matías', 6, 'matias.soto@correo.cl', '123456'),
(19222111, 'Silva', 'Fernanda', 7, 'fernanda.silva@correo.cl', '123456'),
(20444555, 'Torres', 'Diego', 8, 'diego.torres@correo.cl', '123456'),
(18666555, 'Flores', 'Antonia', 9, 'antonia.flores@correo.cl', '123456');

INSERT INTO encargados (id_carrera, apellidos, nombres, correo, telefono, password, cod_invitacion) VALUES
(1, 'Herrera', 'Andrés', 'andres.herrera@u.cl', '+56912345671', '123456', 'ENC-BBB001'),
(1, 'Cárcamo', 'Patricia', 'patricia.carcamo@u.cl', '+56912345672', '123456', 'ENC-BBB002'),
(2, 'Gajardo', 'Roberto', 'roberto.gajardo@u.cl', '+56912345673', '123456', 'ENC-BBB003'),
(3, 'Fuenzalida', 'Marta', 'marta.fuenzalida@u.cl', '+56912345674', '123456', 'ENC-BBB004'),
(4, 'Vergara', 'Gonzalo', 'gonzalo.vergara@u.cl', '+56912345675', '123456', 'ENC-BBB005'),
(5, 'Donoso', 'Cecilia', 'cecilia.donoso@u.cl', '+56912345676', '123456', 'ENC-BBB006'),
(6, 'Poblete', 'Fernando', 'fernando.poblete@u.cl', '+56912345677', '123456', 'ENC-BBB007'),
(7, 'Araya', 'Loreto', 'loreto.araya@u.cl', '+56912345678', '123456', 'ENC-BBB008'),
(8, 'Mendoza', 'Cristián', 'cristian.mendoza@u.cl', '+56912345679', '123456', 'ENC-BBB009'),
(9, 'Miranda', 'Isabel', 'isabel.miranda@u.cl', '+56912345680', '123456', 'ENC-BBB010');

INSERT INTO empresa (nombre, ubicacion, telefono, correo) VALUES
('Tech Solutions Chile', 'Santiago Centro', '+56223456781', 'contacto@techsolutions.cl'), ('Banco de la Nación', 'Las Condes, Santiago', '+56223456782', 'rrhh@banconacion.cl'),
('Hospital Clínico Regional', 'Concepción', '+56412345671', 'practicas@hospital.cl'), ('Consultora Legal Asociados', 'Providencia, Santiago', '+56223456784', 'info@legalasociados.cl'),
('Centro Psicológico Sanamente', 'Viña del Mar', '+56322345671', 'contacto@sanamente.cl'), ('Constructora e Inmobiliaria Siglo XXI', 'Antofagasta', '+56552345671', 'rrhh@sigloxxi.cl'),
('Clínica Santa María', 'Providencia, Santiago', '+56223456787', 'docencia@clinicasantamaria.cl'), ('Agencia Creativa Digital', 'Talca', '+56712345671', 'hola@creativadigital.cl'),
('Centro de Rehabilitación KineVital', 'La Serena', '+56512345671', 'contacto@kinevital.cl'), ('Sistemas Globales Ltda', 'Valparaíso', '+56322345672', 'empleo@sisglobal.cl');

INSERT INTO tutores (id_empresa, apellidos, nombres, cargo, correo, telefono, password) VALUES
(1, 'Tapia', 'Mauricio', 'Jefe de Desarrollo', 'mtapia@techsolutions.cl', '+56981112222', '123456'),
(2, 'Vargas', 'Claudia', 'Subgerente de Finanzas', 'cvargas@banconacion.cl', '+56982223333', '123456'),
(3, 'Henríquez', 'Manuel', 'Jefe de Residentes', 'mhenriquez@hospital.cl', '+56983334444', '123456'),
(4, 'Sanhueza', 'Beatriz', 'Socia Principal', 'bsanhueza@legalasociados.cl', '+56984445555', '123456'),
(5, 'Maldonado', 'Claudio', 'Director Clínico', 'cmaldonado@sanamente.cl', '+56985556666', '123456'),
(6, 'Garrido', 'Héctor', 'Ingeniero Residente', 'hgarrido@sigloxxi.cl', '+56986667777', '123456'),
(7, 'Godoy', 'Patricia', 'Enfermera Coordinadora', 'pgodoy@clinicasantamaria.cl', '+56987778888', '123456'),
(8, 'Ceballos', 'Arturo', 'Director de Arte', 'aceballos@creativadigital.cl', '+56988889999', '123456'),
(9, 'Bustos', 'Valeria', 'Kinesióloga Jefa', 'vbustos@kinevital.cl', '+56989990000', '123456'),
(10, 'Palacios', 'Esteban', 'Líder Técnico', 'epalacios@sisglobal.cl', '+56990001111', '123456');

INSERT INTO estados (nombre) VALUES
('Pendiente'), ('Aceptada'), ('En Curso'), ('Finalizada'), ('Rechazada');

INSERT INTO documentos (titulo, tipo) VALUES
('Convenio de Práctica Firmado', 'PDF'), ('Seguro Escolar de Accidentes', 'PDF'),
('Informe de Avance Mensual', 'Word'), ('Evaluación Final del Tutor', 'PDF'),
('Carta de Aceptación de la Empresa', 'PDF'), ('Bitácora de Horas', 'Excel'),
('Certificado de Alumno Regular', 'PDF'), ('Propuesta de Proyecto de Práctica', 'Word'),
('Informe de Práctica Alumno', 'PDF'), ('Ficha de Inscripción Interna', 'PDF');

INSERT INTO practicas (id_estudiante, id_encargado, id_tutor, fecha_inicio_oferta, fecha_termino_oferta, fecha_inicio_practica, fecha_termino_practica, id_estado, id_documentos) VALUES
(1, 1, 1, '2026-03-01', '2026-03-15', '2026-03-16', '2026-06-16', 4, 1),
(2, 2, 10, '2026-03-01', '2026-03-15', '2026-03-16', '2026-06-16', 3, 2),
(3, 3, 2, '2026-04-01', '2026-04-20', '2026-05-02', '2026-08-02', 3, 5),
(4, 4, 3, '2026-01-10', '2026-01-30', '2026-02-01', '2026-05-01', 4, 4),
(5, 5, 4, '2026-05-10', '2026-05-25', '2026-06-01', '2026-09-01', 3, 8),
(6, 6, 5, '2026-06-01', '2026-06-15', NULL, NULL, 1, 10),
(7, 7, 6, '2026-02-15', '2026-03-01', '2026-03-10', '2026-06-10', 4, 9),
(8, 8, 7, '2026-06-01', '2026-06-18', '2026-06-22', '2026-09-22', 3, 7),
(9, 9, 8, '2026-05-15', '2026-05-30', NULL, NULL, 5, 3),
(10, 10, 9, '2026-06-05', '2026-06-20', NULL, NULL, 2, 5);
