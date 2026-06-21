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

USE gestion_practicas;

-- =========================================================================
-- 1. REGISTROS PARA LA TABLA: universidades (Ya tenías 1, sumamos 9 más)
-- =========================================================================
INSERT INTO universidades (nombre) VALUES 
('Universidad de Santiago'),
('Universidad de Chile'),
('Universidad Católica'),
('Universidad de Concepción'),
('Universidad de Valparaíso'),
('Universidad del Bio-Bío'),
('Universidad de La Serena'),
('Universidad de Antofagasta'),
('Universidad de Talca');

-- =========================================================================
-- 2. REGISTROS PARA LA TABLA: sedes (Ya tenías 1, sumamos 9 más)
-- =========================================================================
INSERT INTO sedes (id_university, ubicacion) VALUES 
(2, 'Campus Sur'),
(2, 'Campus Norte'),
(3, 'Casa Central Santiago'),
(4, 'Campus Concepción'),
(5, 'Sede Reñaca'),
(6, 'Campus Concepción'),
(7, 'Campus Andrés Bello'),
(8, 'Campus Coloso'),
(9, 'Campus Talca');

-- =========================================================================
-- 3. REGISTROS PARA LA TABLA: directores (Ya tenías 1, sumamos 9 más)
-- =========================================================================
INSERT INTO directores (apellidos, nombres, correo, telefono) VALUES 
('Alvarez', 'María', 'maria.alvarez@u.cl', '+56911112222'),
('Soto', 'Carlos', 'carlos.soto@u.cl', '+56922223333'),
('Muñoz', 'Ana', 'ana.munoz@u.cl', '+56933334444'),
('Contreras', 'Pedro', 'pedro.contreras@u.cl', '+56944445555'),
('Morales', 'Luisa', 'luisa.morales@u.cl', '+56955556666'),
('Rojas', 'Jorge', 'jorge.rojas@u.cl', '+56966667777'),
('Silva', 'Elena', 'elena.silva@u.cl', '+56977778888'),
('Castro', 'Ricardo', 'ricardo.castro@u.cl', '+56988889999'),
('López', 'Sofía', 'sofia.lopez@u.cl', '+56999990000');

-- =========================================================================
-- 4. REGISTROS PARA LA TABLA: carreras (Ya tenías 1, sumamos 9 más)
-- =========================================================================
INSERT INTO carreras (nombre, id_sede, id_director) VALUES 
('Ingeniería Civil Industrial', 1, 2),
('Ingeniería Comercial', 2, 3),
('Medicina', 3, 4),
('Derecho', 4, 5),
('Psicología', 5, 6),
('Arquitectura', 6, 7),
('Enfermería', 7, 8),
('Diseño Gráfico', 8, 9),
('Kinesiología', 9, 10);

-- =========================================================================
-- 5. REGISTROS PARA LA TABLA: estudiantes (10 Registros reales)
-- =========================================================================
-- Nota: Si tu campo RUT es INT, puse números válidos sin puntos, guión ni letra K.
INSERT INTO estudiantes (rut, apellidos, nombres, id_carrera, correo) VALUES 
(18234567, 'González', 'Sebastián', 1, 'sebastian.gonzalez@correo.cl'),
(19456123, 'Rodríguez', 'Camila', 1, 'camila.rodriguez@correo.cl'),
(20112345, 'Muñoz', 'Benjamín', 2, 'benjamin.munoz@correo.cl'),
(17987654, 'Rojas', 'Valentina', 3, 'valentina.rojas@correo.cl'),
(19555444, 'Díaz', 'Nicolás', 4, 'nicolas.diaz@correo.cl'),
(20333222, 'Pérez', 'Catalina', 5, 'catalina.perez@correo.cl'),
(18777888, 'Soto', 'Matías', 6, 'matias.soto@correo.cl'),
(19222111, 'Silva', 'Fernanda', 7, 'fernanda.silva@correo.cl'),
(20444555, 'Torres', 'Diego', 8, 'diego.torres@correo.cl'),
(18666555, 'Flores', 'Antonia', 9, 'antonia.flores@correo.cl');

-- =========================================================================
-- 6. REGISTROS PARA LA TABLA: encargados (10 Registros reales)
-- =========================================================================
INSERT INTO encargados (id_carrera, apellidos, nombres, correo, telefono) VALUES 
(1, 'Herrera', 'Andrés', 'andres.herrera@u.cl', '+56912345671'),
(1, 'Cárcamo', 'Patricia', 'patricia.carcamo@u.cl', '+56912345672'),
(2, 'Gajardo', 'Roberto', 'roberto.gajardo@u.cl', '+56912345673'),
(3, 'Fuenzalida', 'Marta', 'marta.fuenzalida@u.cl', '+56912345674'),
(4, 'Vergara', 'Gonzalo', 'gonzalo.vergara@u.cl', '+56912345675'),
(5, 'Donoso', 'Cecilia', 'cecilia.donoso@u.cl', '+56912345676'),
(6, 'Poblete', 'Fernando', 'fernando.poblete@u.cl', '+56912345677'),
(7, 'Araya', 'Loreto', 'loreto.araya@u.cl', '+56912345678'),
(8, 'Mendoza', 'Cristián', 'cristian.mendoza@u.cl', '+56912345679'),
(9, 'Miranda', 'Isabel', 'isabel.miranda@u.cl', '+56912345680');

-- =========================================================================
-- 7. REGISTROS PARA LA TABLA: empresa (10 Registros reales)
-- =========================================================================
INSERT INTO empresa (nombre, ubicacion, telefono, correo) VALUES 
('Tech Solutions Chile', 'Santiago Centro', '+56223456781', 'contacto@techsolutions.cl'),
('Banco de la Nación', 'Las Condes, Santiago', '+56223456782', 'rrhh@banconacion.cl'),
('Hospital Clínico Regional', 'Concepción', '+56412345671', 'practicas@hospital.cl'),
('Consultora Legal Asociados', 'Providencia, Santiago', '+56223456784', 'info@legalasociados.cl'),
('Centro Psicológico Sanamente', 'Viña del Mar', '+56322345671', 'contacto@sanamente.cl'),
('Constructora e Inmobiliaria Siglo XXI', 'Antofagasta', '+56552345671', 'rrhh@sigloxxi.cl'),
('Clínica Santa María', 'Providencia, Santiago', '+56223456787', 'docencia@clinicasantamaria.cl'),
('Agencia Creativa Digital', 'Talca', '+56712345671', 'hola@creativadigital.cl'),
('Centro de Rehabilitación KineVital', 'La Serena', '+56512345671', 'contacto@kinevital.cl'),
('Sistemas Globales Ltda', 'Valparaíso', '+56322345672', 'empleo@sisglobal.cl');

-- =========================================================================
-- 8. REGISTROS PARA LA TABLA: tutores (10 Registros reales)
-- =========================================================================
INSERT INTO tutores (id_empresa, apellidos, nombres, cargo, correo, telefono) VALUES 
(1, 'Tapia', 'Mauricio', 'Jefe de Desarrollo', 'mtapia@techsolutions.cl', '+56981112222'),
(2, 'Vargas', 'Claudia', 'Subgerente de Finanzas', 'cvargas@banconacion.cl', '+56982223333'),
(3, 'Henríquez', 'Manuel', 'Jefe de Residentes', 'mhenriquez@hospital.cl', '+56983334444'),
(4, 'Sanhueza', 'Beatriz', 'Socia Principal', 'bsanhueza@legalasociados.cl', '+56984445555'),
(5, 'Maldonado', 'Claudio', 'Director Clínico', 'cmaldonado@sanamente.cl', '+56985556666'),
(6, 'Garrido', 'Héctor', 'Ingeniero Residente', 'hgarrido@sigloxxi.cl', '+56986667777'),
(7, 'Godoy', 'Patricia', 'Enfermera Coordinadora', 'pgodoy@clinicasantamaria.cl', '+56987778888'),
(8, 'Ceballos', 'Arturo', 'Director de Arte', 'aceballos@creativadigital.cl', '+56988889999'),
(9, 'Bustos', 'Valeria', 'Kinesióloga Jefa', 'vbustos@kinevital.cl', '+56989990000'),
(10, 'Palacios', 'Esteban', 'Líder Técnico', 'epalacios@sisglobal.cl', '+56990001111');

-- =========================================================================
-- 9. REGISTROS PARA LA TABLA: documentos (10 Registros reales)
-- =========================================================================
INSERT INTO documentos (titulo, tipo) VALUES 
('Convenio de Práctica Firmado', 'PDF'),
('Seguro Escolar de Accidentes', 'PDF'),
('Informe de Avance Mensual', 'Word'),
('Evaluación Final del Tutor', 'PDF'),
('Carta de Aceptación de la Empresa', 'PDF'),
('Bitácora de Horas', 'Excel'),
('Certificado de Alumno Regular', 'PDF'),
('Propuesta de Proyecto de Práctica', 'Word'),
('Informe de Práctica Alumno', 'PDF'),
('Ficha de Inscripción Interna', 'PDF');

-- =========================================================================
-- 10. REGISTROS PARA LA TABLA CENTRAL: practicas (10 Registros reales)
-- =========================================================================
-- Relaciona estudiantes, encargados, tutores, estados y documentos
-- Los estados corresponden a: 1=Pendiente, 2=Aceptada, 3=En Curso, 4=Finalizada, 5=Rechazada
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