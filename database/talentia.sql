-- =====================================================================
-- TALENTIA - Base de datos (MySQL 8 / MariaDB 10.5+ de XAMPP)
-- =====================================================================
CREATE DATABASE IF NOT EXISTS talentia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE talentia;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS logs_automatizacion, ranking, correos_enviados, analisis_ia, hojas_vida, candidatos, vacantes, usuarios;
DROP VIEW IF EXISTS v_ultimo_analisis;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('admin','reclutador') NOT NULL DEFAULT 'admin',
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE vacantes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(150) NOT NULL,
  descripcion TEXT,
  requisitos TEXT NOT NULL COMMENT 'Perfil que la IA usa para evaluar',
  activa TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE candidatos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  vacante_id INT UNSIGNED NOT NULL,
  nombres VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  telefono VARCHAR(30),
  ciudad VARCHAR(80),
  estado ENUM('registrado','cv_recibido','en_analisis','preseleccionado','no_seleccionado','error') NOT NULL DEFAULT 'registrado',
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cand_vacante FOREIGN KEY (vacante_id) REFERENCES vacantes(id) ON UPDATE CASCADE,
  INDEX idx_cand_estado (estado)
) ENGINE=InnoDB;

CREATE TABLE hojas_vida (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidato_id INT UNSIGNED NOT NULL,
  archivo VARCHAR(100) NOT NULL COMMENT 'Nombre aleatorio guardado en storage/cv',
  nombre_original VARCHAR(255) NOT NULL,
  tamano_bytes INT UNSIGNED NOT NULL,
  estado_procesamiento ENUM('pendiente','procesando','analizado','error') NOT NULL DEFAULT 'pendiente',
  fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_hv_cand FOREIGN KEY (candidato_id) REFERENCES candidatos(id) ON DELETE CASCADE,
  INDEX idx_hv_estado (estado_procesamiento)
) ENGINE=InnoDB;

CREATE TABLE analisis_ia (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  hoja_vida_id INT UNSIGNED NOT NULL,
  puntuacion TINYINT UNSIGNED NOT NULL,
  resumen TEXT,
  anios_experiencia DECIMAL(4,1) DEFAULT 0,
  experiencia TEXT,
  habilidades JSON,
  formacion JSON,
  certificaciones JSON,
  idiomas JSON,
  fortalezas JSON,
  debilidades JSON,
  recomendacion ENUM('contratar','entrevistar','en_espera','descartar') NOT NULL,
  justificacion TEXT,
  modelo_ia VARCHAR(60),
  fecha_analisis TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_an_hv FOREIGN KEY (hoja_vida_id) REFERENCES hojas_vida(id) ON DELETE CASCADE,
  CONSTRAINT ck_puntuacion CHECK (puntuacion BETWEEN 0 AND 100),
  INDEX idx_an_punt (puntuacion)
) ENGINE=InnoDB;

CREATE TABLE correos_enviados (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidato_id INT UNSIGNED NOT NULL,
  hoja_vida_id INT UNSIGNED NOT NULL,
  tipo ENUM('preseleccion','agradecimiento') NOT NULL,
  destinatario VARCHAR(150) NOT NULL,
  asunto VARCHAR(255) NOT NULL,
  cuerpo MEDIUMTEXT NOT NULL,
  estado_envio ENUM('pendiente','enviado','fallido') NOT NULL DEFAULT 'pendiente',
  error_mensaje VARCHAR(500),
  fecha_envio DATETIME NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_co_cand FOREIGN KEY (candidato_id) REFERENCES candidatos(id) ON DELETE CASCADE,
  CONSTRAINT fk_co_hv FOREIGN KEY (hoja_vida_id) REFERENCES hojas_vida(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ranking (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidato_id INT UNSIGNED NOT NULL UNIQUE,
  hoja_vida_id INT UNSIGNED NOT NULL,
  posicion INT UNSIGNED NOT NULL,
  puntuacion TINYINT UNSIGNED NOT NULL,
  estado ENUM('preseleccionado','no_seleccionado') NOT NULL,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_rk_cand FOREIGN KEY (candidato_id) REFERENCES candidatos(id) ON DELETE CASCADE,
  CONSTRAINT fk_rk_hv FOREIGN KEY (hoja_vida_id) REFERENCES hojas_vida(id) ON DELETE CASCADE,
  INDEX idx_rk_pos (posicion)
) ENGINE=InnoDB;

CREATE TABLE logs_automatizacion (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  workflow VARCHAR(60) NOT NULL,
  hoja_vida_id INT UNSIGNED NULL,
  nivel ENUM('info','warning','error') NOT NULL DEFAULT 'info',
  mensaje VARCHAR(500) NOT NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Último análisis por candidato (un candidato puede subir varios CV)
CREATE VIEW v_ultimo_analisis AS
SELECT h.candidato_id, a.hoja_vida_id, a.id AS analisis_id, a.puntuacion, a.fecha_analisis
FROM analisis_ia a
JOIN hojas_vida h ON h.id = a.hoja_vida_id
WHERE a.id = (SELECT MAX(a2.id) FROM analisis_ia a2
              JOIN hojas_vida h2 ON h2.id = a2.hoja_vida_id
              WHERE h2.candidato_id = h.candidato_id);

-- AUTOMATIZACIÓN 3: n8n solo ejecuta CALL sp_actualizar_ranking();
DROP PROCEDURE IF EXISTS sp_actualizar_ranking;
DELIMITER $$
CREATE PROCEDURE sp_actualizar_ranking()
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
  START TRANSACTION;
  DELETE FROM ranking;
  INSERT INTO ranking (candidato_id, hoja_vida_id, posicion, puntuacion, estado)
  SELECT candidato_id, hoja_vida_id,
         ROW_NUMBER() OVER (ORDER BY puntuacion DESC, fecha_analisis ASC),
         puntuacion,
         IF(puntuacion >= 80, 'preseleccionado', 'no_seleccionado')
  FROM v_ultimo_analisis;
  COMMIT;
END$$
DELIMITER ;

-- Datos iniciales (admin: admin@talentia.local / Admin123*)
INSERT INTO usuarios (nombre,email,password_hash) VALUES
('Administrador','admin@talentia.local','$2b$12$F49r/0VsV59qB6wDt0llhuB87FuixjoGdml9F.6OJE3WRn31h.4lO');

INSERT INTO vacantes (titulo,descripcion,requisitos) VALUES
('Desarrollador Web PHP','Desarrollo de aplicaciones web corporativas.','Mínimo 2 años con PHP y MySQL, HTML/CSS/JavaScript, Git, APIs REST, deseable Laravel, Docker y inglés B1.'),
('Analista de Datos','Análisis y visualización de datos de negocio.','Python o R, SQL avanzado, Power BI o Tableau, estadística, 2 años de experiencia, inglés B2.'),
('Soporte Técnico TI','Atención de incidentes y soporte a usuarios.','Conocimiento de redes, Windows/Linux, mesa de ayuda, buena comunicación, 1 año de experiencia.');
