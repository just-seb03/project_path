/* Procedimientos Almacenados */

DELIMITER //

-- 1. Creación de universidad
CREATE OR REPLACE PROCEDURE crear_universidad(
    IN p_nombre VARCHAR(100)
)
BEGIN 
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error al crear la universidad' AS error;
    END;

    START TRANSACTION;
    INSERT INTO Universidades(nombre) VALUES (p_nombre);
    COMMIT;
    SELECT 'Universidad creada correctamente' AS resultado;
END //

-- 2. Creación de sede
CREATE OR REPLACE PROCEDURE crear_sede(
    IN p_id_universidad INT,
    IN p_ubicacion VARCHAR(50)
)
BEGIN 
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error al crear la sede' AS error;
    END;

    START TRANSACTION;
    INSERT INTO Sedes(id_universidad, ubicacion) VALUES (p_id_universidad, p_ubicacion);
    COMMIT;
    SELECT 'Sede creada correctamente.' AS resultado;
END //

-- 3. Creación de carrera
CREATE OR REPLACE PROCEDURE crear_carrera(
    IN p_nombre VARCHAR(100),
    IN p_id_sede INT,
    IN p_id_director INT
)
BEGIN 
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error al crear la carrera' AS error;
    END;

    START TRANSACTION;
    INSERT INTO Carreras(nombre, id_sede, id_director) VALUES (p_nombre, p_id_sede, p_id_director);
    COMMIT;
    SELECT 'Carrera creada correctamente.' AS resultado;
END //

-- 4. Creación de director
CREATE OR REPLACE PROCEDURE crear_director(
    IN p_apellidos VARCHAR(50),
    IN p_nombres VARCHAR(50),
    IN p_correo VARCHAR(100), 
    IN p_telefono VARCHAR(20),
    IN p_password VARCHAR(255)
)
BEGIN 
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error al crear el director' AS error;
    END;

    START TRANSACTION;
    INSERT INTO Directores(apellidos, nombres, correo, telefono, password)
    VALUES (p_apellidos, p_nombres, p_correo, p_telefono, p_password);
    COMMIT;
    SELECT 'Director creado correctamente' AS resultado;
END //

-- 5. Creación de estudiantes
CREATE OR REPLACE PROCEDURE crear_estudiante(
    IN p_rut INT,
    IN p_apellidos VARCHAR(50),
    IN p_nombres VARCHAR(50),
    IN p_id_carrera INT,
    IN p_correo VARCHAR(100),
    IN p_password VARCHAR(255)
)
BEGIN 
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error al crear el estudiante' AS error;
    END;

    START TRANSACTION;
    INSERT INTO Estudiantes(rut, apellidos, nombres, id_carrera, correo, password)
    VALUES (p_rut, p_apellidos, p_nombres, p_id_carrera, p_correo, p_password);
    COMMIT;
    SELECT 'Estudiante creado correctamente' AS resultado;
END //

-- 6. Creación de encargados
CREATE OR REPLACE PROCEDURE crear_encargado(
    IN p_id_carrera INT,
    IN p_apellidos VARCHAR(50),
    IN p_nombres VARCHAR(50),
    IN p_correo VARCHAR(100),
    IN p_telefono VARCHAR(20),
    IN p_password VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error al crear el encargado' AS error;
    END;

    START TRANSACTION;
    INSERT INTO Encargados(id_carrera, apellidos, nombres, correo, telefono, password)
    VALUES (p_id_carrera, p_apellidos, p_nombres, p_correo, p_telefono, p_password);
    COMMIT;
    SELECT 'Encargado creado correctamente' AS resultado;
END //

-- 7. Creación de estado
CREATE OR REPLACE PROCEDURE crear_estado(
    IN p_nombre VARCHAR(50)
)
BEGIN 
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error al crear el estado' AS error;
    END;

    START TRANSACTION;
    INSERT INTO Estados(nombre) VALUES (p_nombre);
    COMMIT;
    SELECT 'Estado creado correctamente' AS resultado;
END //

-- 8. Creación de practica (Compatible con MySQL sin DEFAULT conflictivos)
CREATE OR REPLACE PROCEDURE crear_practica(
    IN p_id_estudiante INT,
    IN p_id_encargado INT,
    IN p_id_tutor INT,
    IN p_fecha_inicio DATE,
    IN p_fecha_termino DATE,
    IN p_id_estado INT
)
BEGIN 
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error al crear la práctica' AS error;
    END;

    START TRANSACTION;
    
    -- Si se envía 0 o NULL, forzamos de manera interna el estado por defecto (1 = Pendiente)
    IF p_id_estado IS NULL OR p_id_estado = 0 THEN
        SET p_id_estado = 1;
    END IF;

    INSERT INTO Practicas(id_estudiante, id_encargado, id_tutor, fecha_inicio, fecha_termino, id_Estado)
    VALUES (p_id_estudiante, p_id_encargado, p_id_tutor, p_fecha_inicio, p_fecha_termino, p_id_estado);
    
    COMMIT;
    SELECT 'Practica creada correctamente' AS resultado;
END //

-- 9. Creación de tutor
CREATE OR REPLACE PROCEDURE crear_tutor(
    IN p_id_empresa INT,
    IN p_apellidos VARCHAR(50),
    IN p_nombres VARCHAR(50),
    IN p_cargo VARCHAR(50),
    IN p_correo VARCHAR(100),
    IN p_telefono VARCHAR(20),
    IN p_password VARCHAR(255)
)
BEGIN 
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error al crear el tutor' AS error;
    END;

    START TRANSACTION;
    INSERT INTO Tutores(id_empresa, apellidos, nombres, cargo, correo, telefono, password)
    VALUES (p_id_empresa, p_apellidos, p_nombres, p_cargo, p_correo, p_telefono, p_password);
    COMMIT;
    SELECT 'Tutor creado correctamente' AS resultado;
END //

-- 10. Crear Empresa (Mapeado a la tabla "Empresas")
CREATE OR REPLACE PROCEDURE crear_empresa(
    IN p_nombre VARCHAR(100),
    IN p_ubicacion VARCHAR(100),
    IN p_contacto VARCHAR(50) 
)
BEGIN 
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error al crear la empresa' AS error;
    END;

    START TRANSACTION;
    INSERT INTO Empresas(nombre, ubicacion, contacto)
    VALUES (p_nombre, p_ubicacion, p_contacto);
    COMMIT;
    SELECT 'Empresa creada correctamente' AS resultado;
END //

DELIMITER ;

/* Triggers para evitar duplicaciones */

DELIMITER //

-- 1. Evitar Universidades repetidas
CREATE OR REPLACE TRIGGER rep_universidades
BEFORE INSERT ON Universidades
FOR EACH ROW
BEGIN
    IF (SELECT COUNT(*) FROM Universidades WHERE nombre = NEW.nombre) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: La universidad ya se encuentra registrada.';
    END IF;
END //

-- 2. Evitar Directores con datos únicos clonados
CREATE OR REPLACE TRIGGER rep_directores
BEFORE INSERT ON Directores
FOR EACH ROW
BEGIN
    IF (SELECT COUNT(*) FROM Directores WHERE correo = NEW.correo) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El correo del director ya está registrado.';
    ELSEIF NEW.cod_invitacion IS NOT NULL AND (SELECT COUNT(*) FROM Directores WHERE cod_invitacion = NEW.cod_invitacion) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El código de invitación del director ya existe.';
    END IF;
END //

-- 3. Evitar Estados duplicados
CREATE OR REPLACE TRIGGER rep_estados
BEFORE INSERT ON Estados
FOR EACH ROW
BEGIN
    IF (SELECT COUNT(*) FROM Estados WHERE nombre = NEW.nombre) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Este estado ya está registrado.';
    END IF;
END //

-- 4. Evitar Empresas repetidas (Vinculado a "Empresas")
CREATE OR REPLACE TRIGGER rep_empresa
BEFORE INSERT ON Empresas
FOR EACH ROW
BEGIN
    IF (SELECT COUNT(*) FROM Empresas WHERE nombre = NEW.nombre) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: La empresa ya se encuentra registrada.';
    END IF;
END //

-- 5. Evitar Sedes idénticas en la misma Universidad
CREATE OR REPLACE TRIGGER rep_sedes
BEFORE INSERT ON Sedes
FOR EACH ROW
BEGIN
    IF (SELECT COUNT(*) FROM Sedes WHERE id_universidad = NEW.id_universidad AND ubicacion = NEW.ubicacion) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Esta sede ya está registrada para esta universidad.';
    END IF;
END //

-- 6. Evitar Tutores con correos duplicados
CREATE OR REPLACE TRIGGER rep_tutores
BEFORE INSERT ON Tutores
FOR EACH ROW
BEGIN
    IF (SELECT COUNT(*) FROM Tutores WHERE correo = NEW.correo) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El correo del tutor ya está registrado.';
    END IF;
END //

-- 7. Evitar Carreras idénticas en la misma Sede
CREATE OR REPLACE TRIGGER rep_carreras
BEFORE INSERT ON Carreras
FOR EACH ROW
BEGIN
    IF (SELECT COUNT(*) FROM Carreras WHERE nombre = NEW.nombre AND id_sede = NEW.id_sede) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Esta carrera ya existe en la sede seleccionada.';
    END IF;
END //

-- 8. Evitar Estudiantes repetidos (RUT o Correo)
CREATE OR REPLACE TRIGGER rep_estudiantes
BEFORE INSERT ON Estudiantes
FOR EACH ROW
BEGIN
    IF (SELECT COUNT(*) FROM Estudiantes WHERE rut = NEW.rut) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El RUT del estudiante ya está registrado.';
    ELSEIF (SELECT COUNT(*) FROM Estudiantes WHERE correo = NEW.correo) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El correo del estudiante ya está registrado.';
    END IF;
END //

-- 9. Evitar Encargados clonados
CREATE OR REPLACE TRIGGER rep_encargados
BEFORE INSERT ON Encargados
FOR EACH ROW
BEGIN
    IF (SELECT COUNT(*) FROM Encargados WHERE correo = NEW.correo) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El correo del encargado ya está registrado.';
    ELSEIF NEW.cod_invitacion IS NOT NULL AND (SELECT COUNT(*) FROM Encargados WHERE cod_invitacion = NEW.cod_invitacion) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El código de invitación del encargado ya existe.';
    END IF;
END //

-- 10. Controlar Prácticas simultáneas activas ('En Curso' = Estado 2)
CREATE OR REPLACE TRIGGER rep_practicas
BEFORE INSERT ON Practicas
FOR EACH ROW
BEGIN
    IF NEW.id_Estado = 2 AND (SELECT COUNT(*) FROM Practicas WHERE id_estudiante = NEW.id_estudiante AND id_Estado = 2) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El estudiante ya cuenta con una práctica activa En Curso.';
    END IF;
END //

DELIMITER ;