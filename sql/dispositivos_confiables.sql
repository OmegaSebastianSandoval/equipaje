-- Dispositivos en los que el usuario marcó "Recordar este dispositivo" tras validar el OTP.
-- Solo se guarda el hash SHA-256 del token; el token real vive únicamente en la cookie del navegador.
CREATE TABLE IF NOT EXISTS `dispositivos_confiables` (
  `dispositivo_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `dispositivo_usuario` INT NOT NULL,
  `dispositivo_token` CHAR(64) NOT NULL,
  `dispositivo_navegador` VARCHAR(255) DEFAULT NULL,
  `dispositivo_ip` VARCHAR(45) DEFAULT NULL,
  `dispositivo_fecha` DATETIME NOT NULL,
  `dispositivo_ultimo_uso` DATETIME DEFAULT NULL,
  `dispositivo_vence` DATETIME NOT NULL,
  PRIMARY KEY (`dispositivo_id`),
  UNIQUE KEY `uk_dispositivo_token` (`dispositivo_token`),
  KEY `idx_dispositivo_usuario` (`dispositivo_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
