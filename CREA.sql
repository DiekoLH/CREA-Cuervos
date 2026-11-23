CREATE DATABASE IF NOT EXISTS CREA CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE CREA;

-- 1. Tabla datosEstudiante
CREATE TABLE IF NOT EXISTS datosEstudiante (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    matricula VARCHAR(30) NOT NULL UNIQUE,
    carrera VARCHAR(100) NOT NULL,
    correo VARCHAR(120) NOT NULL UNIQUE,
    grupo VARCHAR(30) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol VARCHAR(30) NOT NULL DEFAULT 'Estudiante'
);

-- 2. Tabla datosInstructor
CREATE TABLE IF NOT EXISTS datosInstructor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    identificacion VARCHAR(40) NOT NULL UNIQUE,
    correo VARCHAR(120) NOT NULL UNIQUE,
    taller_impartido VARCHAR(100) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol VARCHAR(30) NOT NULL DEFAULT 'Instructor'
);

-- 3. Tabla taller (alumnos inscritos por taller)
CREATE TABLE IF NOT EXISTS taller (
    id INT AUTO_INCREMENT PRIMARY KEY,
    taller VARCHAR(100) NOT NULL,
    matricula VARCHAR(30) NOT NULL,
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_taller_matricula (taller, matricula),
    FOREIGN KEY (matricula) REFERENCES datosEstudiante(matricula) ON DELETE CASCADE ON UPDATE CASCADE
);

-- 4. Tabla solicitudJustificar
CREATE TABLE IF NOT EXISTS solicitudJustificar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula VARCHAR(30) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    taller VARCHAR(100) DEFAULT 'Teatro',
    fecha DATE NOT NULL,
    justificante VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Tabla estadoJust (estado de la justificación visible para el alumno)
CREATE TABLE IF NOT EXISTS estadoJust (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula VARCHAR(30) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    fecha DATE NOT NULL,
    justificante VARCHAR(255) NOT NULL,
    estado ENUM('Pendiente', 'Aceptado', 'Rechazado') NOT NULL DEFAULT 'Pendiente',
    observaciones TEXT
);

-- 6. Tabla justificar (bandeja de justificantes del instructor)
CREATE TABLE IF NOT EXISTS justificar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula VARCHAR(30) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    taller VARCHAR(100) DEFAULT 'Teatro',
    fecha DATE NOT NULL,
    justificante VARCHAR(255) NOT NULL,
    estado ENUM('Pendiente', 'Aceptado', 'Rechazado') NOT NULL DEFAULT 'Pendiente',
    observaciones TEXT
);

-- 7. Tabla constanciasTaller
CREATE TABLE IF NOT EXISTS constanciasTaller (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula VARCHAR(30) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    taller VARCHAR(100) NOT NULL,
    estado ENUM('Con constancia', 'Sin constancia') NOT NULL DEFAULT 'Sin constancia',
    constancia VARCHAR(255) DEFAULT NULL,
    UNIQUE KEY uq_constancia_alumno_taller (matricula, taller)
);

-- 8. Tabla hasConstancia (alumnos que ya tienen constancia emitida)
CREATE TABLE IF NOT EXISTS hasConstancia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula VARCHAR(30) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    taller VARCHAR(100) NOT NULL,
    fecha_emision VARCHAR(50) NOT NULL,
    constancia VARCHAR(255) NOT NULL
);

-- 9. Tabla avisos
CREATE TABLE IF NOT EXISTS avisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo_aviso VARCHAR(150) NOT NULL,
    contenido TEXT NOT NULL,
    imagen VARCHAR(255) DEFAULT NULL,
    taller VARCHAR(100) DEFAULT 'General',
    fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 10. Tabla tomaAsist (acumula +1 en asistencia, falta o falta_justificada por cada toma)
CREATE TABLE IF NOT EXISTS tomaAsist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    taller VARCHAR(100) NOT NULL,
    fecha DATE NOT NULL,
    matricula VARCHAR(30) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    asistencia INT NOT NULL DEFAULT 0,
    falta INT NOT NULL DEFAULT 0,
    falta_justificada INT NOT NULL DEFAULT 0,
    UNIQUE KEY uq_toma_taller_fecha_alumno (taller, fecha, nombre)
);

-- 11. Tabla listaAsist (estado Presente o Ausente según tomaAsist)
CREATE TABLE IF NOT EXISTS listaAsist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    taller VARCHAR(100) NOT NULL,
    fecha DATE NOT NULL,
    matricula VARCHAR(30) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    estado_asistencia ENUM('Presente', 'Ausente') NOT NULL,
    UNIQUE KEY uq_lista_taller_fecha_alumno (taller, fecha, nombre)
);

-- ==========================================
-- CONSULTAS INSERT DE PRUEBA
-- ==========================================

INSERT INTO datosEstudiante (nombre, matricula, carrera, correo, grupo, contrasena, rol) VALUES
('Paola Jimenez', '20241001', 'Desarrollo de Software', 'paola.jimenez@utvt.edu.mx', 'DSM-41', '1234', 'Estudiante'),
('Miguel Orona', '20241002', 'Desarrollo de Software', 'miguel.orona@utvt.edu.mx', 'DSM-41', '1234', 'Estudiante'),
('Andrea Martínez', '20241003', 'Mecatrónica', 'andrea.martinez@utvt.edu.mx', 'MEC-41', '1234', 'Estudiante'),
('Daniel Hernández', '20241004', 'Infraestructura de Redes', 'daniel.hernandez@utvt.edu.mx', 'IRD-41', '1234', 'Estudiante'),
('Fernanda López', '20241005', 'Procesos Productivos', 'fernanda.lopez@utvt.edu.mx', 'PP-41', '1234', 'Estudiante'),
('Ricardo Sánchez', '20241006', 'Desarrollo de Software', 'ricardo.sanchez@utvt.edu.mx', 'DSM-42', '1234', 'Estudiante'),
('Daniela Ramírez', '20241007', 'Paramédico', 'daniela.ramirez@utvt.edu.mx', 'PAR-41', '1234', 'Estudiante');

INSERT INTO datosInstructor (nombre, identificacion, correo, taller_impartido, contrasena, rol) VALUES
('Mauricio Torres Bernal', 'INST-001', 'mauricio.torres@utvt.edu.mx', 'Teatro', '1234', 'Instructor'),
('José Pablo Hernández Sánchez', 'INST-002', 'pablo.hernandez@utvt.edu.mx', 'Danza y baile', '1234', 'Instructor');

INSERT INTO taller (taller, matricula) VALUES
('Teatro', '20241001'),
('Teatro', '20241002'),
('Teatro', '20241003'),
('Teatro', '20241004'),
('Teatro', '20241005'),
('Teatro', '20241006'),
('Teatro', '20241007');

INSERT INTO constanciasTaller (matricula, nombre, taller, estado, constancia) VALUES
('20241001', 'Paola Jimenez', 'Teatro', 'Sin constancia', NULL),
('20241002', 'Miguel Orona', 'Teatro', 'Sin constancia', NULL),
('20241003', 'Andrea Martínez', 'Teatro', 'Con constancia', 'imgs/constacia.pdf'),
('20241004', 'Daniel Hernández', 'Teatro', 'Sin constancia', NULL),
('20241005', 'Fernanda López', 'Teatro', 'Con constancia', 'imgs/constacia.pdf'),
('20241006', 'Ricardo Sánchez', 'Teatro', 'Sin constancia', NULL),
('20241007', 'Daniela Ramírez', 'Teatro', 'Con constancia', 'imgs/constacia.pdf');

INSERT INTO hasConstancia (matricula, nombre, taller, fecha_emision, constancia) VALUES
('20241003', 'Andrea Martínez', 'Teatro', 'Octubre 2026', 'imgs/constacia.pdf'),
('20241005', 'Fernanda López', 'Teatro', 'Octubre 2026', 'imgs/constacia.pdf'),
('20241007', 'Daniela Ramírez', 'Teatro', 'Octubre 2026', 'imgs/constacia.pdf');

INSERT INTO tomaAsist (taller, fecha, matricula, nombre, asistencia, falta, falta_justificada) VALUES
('Teatro', '2026-11-07', '20241001', 'Paola Jimenez', 1, 0, 0),
('Teatro', '2026-11-07', '20241002', 'Miguel Orona', 1, 0, 0),
('Teatro', '2026-11-07', '20241003', 'Andrea Martínez', 0, 1, 0),
('Teatro', '2026-11-07', '20241004', 'Daniel Hernández', 1, 0, 0),
('Teatro', '2026-11-07', '20241005', 'Fernanda López', 1, 0, 0),
('Teatro', '2026-11-07', '20241006', 'Ricardo Sánchez', 0, 1, 0),
('Teatro', '2026-11-07', '20241007', 'Daniela Ramírez', 1, 0, 0);

INSERT INTO listaAsist (taller, fecha, matricula, nombre, estado_asistencia) VALUES
('Teatro', '2026-11-07', '20241001', 'Paola Jimenez', 'Presente'),
('Teatro', '2026-11-07', '20241002', 'Miguel Orona', 'Presente'),
('Teatro', '2026-11-07', '20241003', 'Andrea Martínez', 'Ausente'),
('Teatro', '2026-11-07', '20241004', 'Daniel Hernández', 'Presente'),
('Teatro', '2026-11-07', '20241005', 'Fernanda López', 'Presente'),
('Teatro', '2026-11-07', '20241006', 'Ricardo Sánchez', 'Ausente'),
('Teatro', '2026-11-07', '20241007', 'Daniela Ramírez', 'Presente');

INSERT INTO solicitudJustificar (matricula, nombre, taller, fecha, justificante) VALUES
('20241001', 'Paola Jimenez', 'Teatro', '2026-10-12', 'Constancia médica.pdf'),
('20241006', 'Ricardo Sánchez', 'Teatro', '2026-10-15', 'Comprobante de cita.pdf');

INSERT INTO justificar (matricula, nombre, taller, fecha, justificante, estado, observaciones) VALUES
('20241001', 'Paola Jimenez', 'Teatro', '2026-10-12', 'Constancia médica.pdf', 'Pendiente', ''),
('20241006', 'Ricardo Sánchez', 'Teatro', '2026-10-15', 'Comprobante de cita.pdf', 'Pendiente', '');

INSERT INTO estadoJust (matricula, nombre, fecha, justificante, estado, observaciones) VALUES
('20241001', 'Paola Jimenez', '2026-10-12', 'Constancia médica.pdf', 'Pendiente', 'En revisión por el instructor'),
('20241006', 'Ricardo Sánchez', '2026-10-15', 'Comprobante de cita.pdf', 'Pendiente', '');

INSERT INTO avisos (titulo_aviso, contenido, imagen, taller) VALUES
('Ensayo general', 'Se informa a los integrantes que deberán asistir al ensayo general para preparar la próxima presentación.', NULL, 'Música'),
('Exposición de trabajos', 'Los estudiantes presentarán sus proyectos realizados durante el taller.', 'imgs/Evento_Artes.jpeg', 'Teatro');
