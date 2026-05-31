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