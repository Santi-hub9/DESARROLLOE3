-- =====================================================================
-- TALENTIA - Migración del tercer corte (ejecutar DESPUÉS de talentia.sql)
-- =====================================================================
USE talentia;

ALTER TABLE candidatos
  ADD COLUMN documento_identidad VARCHAR(20) NULL AFTER email,
  ADD COLUMN documento_verificado ENUM('no_enviado','pendiente','validado','rechazado','revision_manual') NOT NULL DEFAULT 'no_enviado',
  ADD COLUMN autorizacion_datos TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Ley 1581: autorización de tratamiento',
  ADD COLUMN fecha_autorizacion DATETIME NULL;

ALTER TABLE correos_enviados
  MODIFY tipo ENUM('preseleccion','agradecimiento','documento_validado','documento_rechazado') NOT NULL,
  MODIFY hoja_vida_id INT UNSIGNED NULL;

-- Cola de tareas: reemplaza los webhooks de n8n
CREATE TABLE cola_tareas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tipo VARCHAR(40) NOT NULL,
  payload JSON NOT NULL,
  estado ENUM('pendiente','procesando','completada','fallida') NOT NULL DEFAULT 'pendiente',
  intentos TINYINT UNSIGNED NOT NULL DEFAULT 0,
  max_intentos TINYINT UNSIGNED NOT NULL DEFAULT 4,
  proximo_intento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  bloqueada_en DATETIME NULL,
  ultimo_error VARCHAR(500) NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_cola (estado, proximo_intento)
) ENGINE=InnoDB;

-- Registro de cada ejecución: fecha, entrada y resultado
CREATE TABLE ejecuciones_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tarea_id INT UNSIGNED NULL,
  flujo VARCHAR(40) NOT NULL,
  estado ENUM('ok','reintento','error') NOT NULL,
  entrada TEXT,
  resultado TEXT,
  duracion_ms INT UNSIGNED DEFAULT 0,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_tarea FOREIGN KEY (tarea_id) REFERENCES cola_tareas(id) ON DELETE SET NULL,
  INDEX idx_log_fecha (fecha)
) ENGINE=InnoDB;

-- Auditoría de cada llamada a IA (no guarda el contenido, solo su hash)
CREATE TABLE auditoria_ia (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  accion VARCHAR(40) NOT NULL,
  proveedor VARCHAR(30) NOT NULL,
  modelo VARCHAR(60),
  candidato_id INT UNSIGNED NULL,
  entrada_hash CHAR(64) NOT NULL,
  resumen_salida VARCHAR(500),
  estado ENUM('ok','fallback','error') NOT NULL,
  duracion_ms INT UNSIGNED DEFAULT 0,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_aud_fecha (fecha)
) ENGINE=InnoDB;

-- Módulo de visión: verificación de documentos de identidad
CREATE TABLE documentos_identidad (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidato_id INT UNSIGNED NOT NULL,
  tipo ENUM('cedula','certificado') NOT NULL DEFAULT 'cedula',
  archivo VARCHAR(100) NOT NULL,
  subido_por_tipo ENUM('candidato','usuario') NOT NULL DEFAULT 'candidato',
  subido_por_id INT UNSIGNED NOT NULL,
  ip VARCHAR(45),
  autorizo_externo TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Permite enviar la imagen a un servicio externo',
  estado ENUM('pendiente','procesando','validado','rechazado','revision_manual','error') NOT NULL DEFAULT 'pendiente',
  proveedor VARCHAR(30),
  datos_extraidos JSON NULL,
  confianza DECIMAL(4,3) NULL,
  coincidencias JSON NULL,
  revisado_por INT UNSIGNED NULL,
  fecha_revision DATETIME NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_doc_cand FOREIGN KEY (candidato_id) REFERENCES candidatos(id) ON DELETE CASCADE,
  CONSTRAINT fk_doc_rev FOREIGN KEY (revisado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
  INDEX idx_doc_estado (estado)
) ENGINE=InnoDB;
