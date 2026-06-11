-- Tabla de auditoria general (Trazabilidad)
CREATE TABLE Auditoria (
    id_auditoria INT PRIMARY KEY AUTO_INCREMENT,
    tabla_afectada VARCHAR(100) NOT NULL,
    operacion ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    id_registro_afectado INT NOT NULL,
    datos_anteriores JSON NULL,
    datos_nuevos JSON NULL,
    usuario VARCHAR(100) DEFAULT (CURRENT_USER()),
    fecha_hora DATETIME DEFAULT (NOW())
);

/* Triggers por tablas (INSERT, UPDATE, DELETE) */

DELIMITER //
-- Tabla Universidades

-- INSERT
CREATE OR REPLACE TRIGGER aud_universidades_insert
AFTER INSERT ON Universidades
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Universidades', 
        'INSERT', 
        NEW.id_universidad, 
        NULL, 
        JSON_OBJECT('id_universidad', NEW.id_universidad, 'nombre', NEW.nombre)
    );
END //

-- UPDATE
CREATE OR REPLACE TRIGGER aud_universidades_update
AFTER UPDATE ON Universidades
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Universidades', 
        'UPDATE', 
        NEW.id_universidad, 
        JSON_OBJECT('id_universidad', OLD.id_universidad, 'nombre', OLD.nombre), 
        JSON_OBJECT('id_universidad', NEW.id_universidad, 'nombre', NEW.nombre)
    );
END //

-- DELETE
CREATE OR REPLACE TRIGGER aud_universidades_delete
AFTER DELETE ON Universidades
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Universidades', 
        'DELETE', 
        OLD.id_universidad, 
        JSON_OBJECT('id_universidad', OLD.id_universidad, 'nombre', OLD.nombre), 
        NULL
    );
END //

-- Tabla de Empresas

-- INSERT
CREATE OR REPLACE TRIGGER aud_empresas_insert
AFTER INSERT ON Empresas
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Empresas', 'INSERT', NEW.id_empresa, NULL, 
        JSON_OBJECT('id_empresa', NEW.id_empresa, 'nombre', NEW.nombre, 'ubicacion', NEW.ubicacion, 'contacto', NEW.contacto)
    );
END //

-- UPDATE
CREATE OR REPLACE TRIGGER aud_empresas_update
AFTER UPDATE ON Empresas
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Empresas', 'UPDATE', NEW.id_empresa, 
        JSON_OBJECT('id_empresa', OLD.id_empresa, 'nombre', OLD.nombre, 'ubicacion', OLD.ubicacion, 'contacto', OLD.contacto), 
        JSON_OBJECT('id_empresa', NEW.id_empresa, 'nombre', NEW.nombre, 'ubicacion', NEW.ubicacion, 'contacto', NEW.contacto)
    );
END //

-- DELETE
CREATE OR REPLACE TRIGGER aud_empresas_delete
AFTER DELETE ON Empresas
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Empresas', 'DELETE', OLD.id_empresa, 
        JSON_OBJECT('id_empresa', OLD.id_empresa, 'nombre', OLD.nombre, 'ubicacion', OLD.ubicacion, 'contacto', OLD.contacto), 
        NULL
    );
END //

-- Tabla de Estudiantes

-- INSERT
CREATE OR REPLACE TRIGGER aud_estudiantes_insert
AFTER INSERT ON Estudiantes
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Estudiantes', 'INSERT', NEW.id_estudiante, NULL, 
        JSON_OBJECT('id_estudiante', NEW.id_estudiante, 'rut', NEW.rut, 'nombres', NEW.nombres, 'apellidos', NEW.apellidos, 'correo', NEW.correo)
    );
END //

-- UPDATE
CREATE OR REPLACE TRIGGER aud_estudiantes_update
AFTER UPDATE ON Estudiantes
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Estudiantes', 'UPDATE', NEW.id_estudiante, 
        JSON_OBJECT('id_estudiante', OLD.id_estudiante, 'rut', OLD.rut, 'nombres', OLD.nombres, 'apellidos', OLD.apellidos, 'correo', OLD.correo), 
        JSON_OBJECT('id_estudiante', NEW.id_estudiante, 'rut', NEW.rut, 'nombres', NEW.nombres, 'apellidos', NEW.apellidos, 'correo', NEW.correo)
    );
END //

-- DELETE
CREATE OR REPLACE TRIGGER aud_estudiantes_delete
AFTER DELETE ON Estudiantes
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Estudiantes', 'DELETE', OLD.id_estudiante, 
        JSON_OBJECT('id_estudiante', OLD.id_estudiante, 'rut', OLD.rut, 'nombres', OLD.nombres, 'apellidos', OLD.apellidos, 'correo', OLD.correo), 
        NULL
    );
END //

-- Tabla Prácticas

-- INSERT
CREATE OR REPLACE TRIGGER aud_practicas_insert
AFTER INSERT ON Practicas
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Practicas', 'INSERT', NEW.id_practica, NULL, 
        JSON_OBJECT('id_practica', NEW.id_practica, 'id_estudiante', NEW.id_estudiante, 'fecha_inicio', NEW.fecha_inicio, 'id_Estado', NEW.id_Estado)
    );
END //

-- UPDATE
CREATE OR REPLACE TRIGGER aud_practicas_update
AFTER UPDATE ON Practicas
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Practicas', 'UPDATE', NEW.id_practica, 
        JSON_OBJECT('id_practica', OLD.id_practica, 'id_estudiante', OLD.id_estudiante, 'fecha_inicio', OLD.fecha_inicio, 'fecha_termino', OLD.fecha_termino, 'id_Estado', OLD.id_Estado), 
        JSON_OBJECT('id_practica', NEW.id_practica, 'id_estudiante', NEW.id_estudiante, 'fecha_inicio', NEW.fecha_inicio, 'fecha_termino', NEW.fecha_termino, 'id_Estado', NEW.id_Estado)
    );
END //

-- DELETE
CREATE OR REPLACE TRIGGER aud_practicas_delete
AFTER DELETE ON Practicas
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Practicas', 'DELETE', OLD.id_practica, 
        JSON_OBJECT('id_practica', OLD.id_practica, 'id_estudiante', OLD.id_estudiante, 'fecha_inicio', OLD.fecha_inicio, 'id_Estado', OLD.id_Estado), 
        NULL
    );
END //

-- Tabla Sedes

-- INSERT
CREATE OR REPLACE TRIGGER aud_sedes_insert
AFTER INSERT ON Sedes
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Sedes', 'INSERT', NEW.id_sede, NULL, 
        JSON_OBJECT('id_sede', NEW.id_sede, 'id_universidad', NEW.id_universidad, 'ubicacion', NEW.ubicacion)
    );
END //

-- UPDATE
CREATE OR REPLACE TRIGGER aud_sedes_update
AFTER UPDATE ON Sedes
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Sedes', 'UPDATE', NEW.id_sede, 
        JSON_OBJECT('id_sede', OLD.id_sede, 'id_universidad', OLD.id_universidad, 'ubicacion', OLD.ubicacion), 
        JSON_OBJECT('id_sede', NEW.id_sede, 'id_universidad', NEW.id_universidad, 'ubicacion', NEW.ubicacion)
    );
END //

-- DELETE
CREATE OR REPLACE TRIGGER aud_sedes_delete
AFTER DELETE ON Sedes
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Sedes', 'DELETE', OLD.id_sede, 
        JSON_OBJECT('id_sede', OLD.id_sede, 'id_universidad', OLD.id_universidad, 'ubicacion', OLD.ubicacion), 
        NULL
    );
END //

-- Tabla Carreras

-- INSERT
CREATE OR REPLACE TRIGGER aud_carreras_insert
AFTER INSERT ON Carreras
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Carreras', 'INSERT', NEW.id_carrera, NULL, 
        JSON_OBJECT('id_carrera', NEW.id_carrera, 'nombre', NEW.nombre, 'id_sede', NEW.id_sede, 'id_director', NEW.id_director)
    );
END //

-- UPDATE
CREATE OR REPLACE TRIGGER aud_carreras_update
AFTER UPDATE ON Carreras
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Carreras', 'UPDATE', NEW.id_carrera, 
        JSON_OBJECT('id_carrera', OLD.id_carrera, 'nombre', OLD.nombre, 'id_sede', OLD.id_sede, 'id_director', OLD.id_director), 
        JSON_OBJECT('id_carrera', NEW.id_carrera, 'nombre', NEW.nombre, 'id_sede', NEW.id_sede, 'id_director', NEW.id_director)
    );
END //

-- DELETE
CREATE OR REPLACE TRIGGER aud_carreras_delete
AFTER DELETE ON Carreras
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Carreras', 'DELETE', OLD.id_carrera, 
        JSON_OBJECT('id_carrera', OLD.id_carrera, 'nombre', OLD.nombre, 'id_sede', OLD.id_sede, 'id_director', OLD.id_director), 
        NULL
    );
END //

-- Tabla Directores

-- INSERT
CREATE OR REPLACE TRIGGER aud_directores_insert
AFTER INSERT ON Directores
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Directores', 'INSERT', NEW.id_director, NULL, 
        JSON_OBJECT('id_director', NEW.id_director, 'nombres', NEW.nombres, 'apellidos', NEW.apellidos, 'correo', NEW.correo, 'cod_invitacion', NEW.cod_invitacion)
    );
END //

-- UPDATE
CREATE OR REPLACE TRIGGER aud_directores_update
AFTER UPDATE ON Directores
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Directores', 'UPDATE', NEW.id_director, 
        JSON_OBJECT('id_director', OLD.id_director, 'nombres', OLD.nombres, 'apellidos', OLD.apellidos, 'correo', OLD.correo, 'cod_invitacion', OLD.cod_invitacion), 
        JSON_OBJECT('id_director', NEW.id_director, 'nombres', NEW.nombres, 'apellidos', NEW.apellidos, 'correo', NEW.correo, 'cod_invitacion', NEW.cod_invitacion)
    );
END //

-- DELETE
CREATE OR REPLACE TRIGGER aud_directores_delete
AFTER DELETE ON Directores
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Directores', 'DELETE', OLD.id_director, 
        JSON_OBJECT('id_director', OLD.id_director, 'nombres', OLD.nombres, 'apellidos', OLD.apellidos, 'correo', OLD.correo, 'cod_invitacion', OLD.cod_invitacion), 
        NULL
    );
END //

-- Tabla Encargados

-- INSERT
CREATE OR REPLACE TRIGGER aud_encargados_insert
AFTER INSERT ON Encargados
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Encargados', 'INSERT', NEW.id_encargado, NULL, 
        JSON_OBJECT('id_encargado', NEW.id_encargado, 'id_carrera', NEW.id_carrera, 'nombres', NEW.nombres, 'apellidos', NEW.apellidos, 'correo', NEW.correo)
    );
END //

-- UPDATE
CREATE OR REPLACE TRIGGER aud_encargados_update
AFTER UPDATE ON Encargados
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Encargados', 'UPDATE', NEW.id_encargado, 
        JSON_OBJECT('id_encargado', OLD.id_encargado, 'id_carrera', OLD.id_carrera, 'nombres', OLD.nombres, 'apellidos', OLD.apellidos, 'correo', OLD.correo), 
        JSON_OBJECT('id_encargado', NEW.id_encargado, 'id_carrera', NEW.id_carrera, 'nombres', NEW.nombres, 'apellidos', NEW.apellidos, 'correo', NEW.correo)
    );
END //

-- DELETE
CREATE OR REPLACE TRIGGER aud_encargados_delete
AFTER DELETE ON Encargados
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Encargados', 'DELETE', OLD.id_encargado, 
        JSON_OBJECT('id_encargado', OLD.id_encargado, 'id_carrera', OLD.id_carrera, 'nombres', OLD.nombres, 'apellidos', OLD.apellidos, 'correo', OLD.correo), 
        NULL
    );
END //

-- Tabla Tutores

-- INSERT
CREATE OR REPLACE TRIGGER aud_tutores_insert
AFTER INSERT ON Tutores
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Tutores', 'INSERT', NEW.id_tutor, NULL, 
        JSON_OBJECT('id_tutor', NEW.id_tutor, 'id_empresa', NEW.id_empresa, 'nombres', NEW.nombres, 'apellidos', NEW.apellidos, 'cargo', NEW.cargo)
    );
END //

-- UPDATE
CREATE OR REPLACE TRIGGER aud_tutores_update
AFTER UPDATE ON Tutores
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Tutores', 'UPDATE', NEW.id_tutor, 
        JSON_OBJECT('id_tutor', OLD.id_tutor, 'id_empresa', OLD.id_empresa, 'nombres', OLD.nombres, 'apellidos', OLD.apellidos, 'cargo', OLD.cargo), 
        JSON_OBJECT('id_tutor', NEW.id_tutor, 'id_empresa', NEW.id_empresa, 'nombres', NEW.nombres, 'apellidos', NEW.apellidos, 'cargo', NEW.cargo)
    );
END //

-- DELETE
CREATE OR REPLACE TRIGGER aud_tutores_delete
AFTER DELETE ON Tutores
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Tutores', 'DELETE', OLD.id_tutor, 
        JSON_OBJECT('id_tutor', OLD.id_tutor, 'id_empresa', OLD.id_empresa, 'nombres', OLD.nombres, 'apellidos', OLD.apellidos, 'cargo', OLD.cargo), 
        NULL
    );
END //

-- Tabla Estados

-- INSERT
CREATE OR REPLACE TRIGGER aud_estados_insert
AFTER INSERT ON Estados
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Estados', 'INSERT', NEW.id_estado, NULL, 
        JSON_OBJECT('id_estado', NEW.id_estado, 'nombre', NEW.nombre)
    );
END //

-- UPDATE
CREATE OR REPLACE TRIGGER aud_estados_update
AFTER UPDATE ON Estados
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Estados', 'UPDATE', NEW.id_estado, 
        JSON_OBJECT('id_estado', OLD.id_estado, 'nombre', OLD.nombre), 
        JSON_OBJECT('id_estado', NEW.id_estado, 'nombre', NEW.nombre)
    );
END //

-- DELETE
CREATE OR REPLACE TRIGGER aud_estados_delete
AFTER DELETE ON Estados
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'Estados', 'DELETE', OLD.id_estado, 
        JSON_OBJECT('id_estado', OLD.id_estado, 'nombre', OLD.nombre), 
        NULL
    );
END //

-- Tabla recuperación pass

-- INSERT
CREATE OR REPLACE TRIGGER aud_recuperacion_insert
AFTER INSERT ON recuperacion_pass
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'recuperacion_pass', 'INSERT', NEW.id, NULL, 
        JSON_OBJECT('id', NEW.id, 'correo', NEW.correo, 'expiracion', NEW.expiracion)
    );
END //

-- UPDATE
CREATE OR REPLACE TRIGGER aud_recuperacion_update
AFTER UPDATE ON recuperacion_pass
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'recuperacion_pass', 'UPDATE', NEW.id, 
        JSON_OBJECT('id', OLD.id, 'correo', OLD.correo, 'expiracion', OLD.expiracion), 
        JSON_OBJECT('id', NEW.id, 'correo', NEW.correo, 'expiracion', NEW.expiracion)
    );
END //

-- DELETE
CREATE OR REPLACE TRIGGER aud_recuperacion_delete
AFTER DELETE ON recuperacion_pass
FOR EACH ROW
BEGIN
    INSERT INTO Auditoria (tabla_afectada, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
    VALUES (
        'recuperacion_pass', 'DELETE', OLD.id, 
        JSON_OBJECT('id', OLD.id, 'correo', OLD.correo, 'expiracion', OLD.expiracion), 
        NULL
    );
END //

DELIMITER ;
