-- Registro/entrega por lotes, vuelos del XML FIDS y alertas de salida.
-- Ver PLAN-vuelos-y-lotes.md

-- 4.1 aerolineas: codigo IATA para cruzar con el linecode del XML
ALTER TABLE aerolineas ADD aerolinea_iata VARCHAR(3) NULL AFTER aerolinea_codigo;
UPDATE aerolineas SET aerolinea_codigo = '019' WHERE aerolinea_codigo = '&nbsp;019'; -- dato corrupto de Satena

UPDATE aerolineas SET aerolinea_iata = 'AA' WHERE aerolinea_codigo = '001';
UPDATE aerolineas SET aerolinea_iata = 'DL' WHERE aerolinea_codigo = '006';
UPDATE aerolineas SET aerolinea_iata = 'AC' WHERE aerolinea_codigo = '014';
UPDATE aerolineas SET aerolinea_iata = 'UA' WHERE aerolinea_codigo = '016';
UPDATE aerolineas SET aerolinea_iata = '9R' WHERE aerolinea_codigo = '019';
UPDATE aerolineas SET aerolinea_iata = 'Y4' WHERE aerolinea_codigo = '036';
UPDATE aerolineas SET aerolinea_iata = 'AR' WHERE aerolinea_codigo = '044';
UPDATE aerolineas SET aerolinea_iata = 'LA' WHERE aerolinea_codigo = '045';
UPDATE aerolineas SET aerolinea_iata = 'AF' WHERE aerolinea_codigo = '057';
UPDATE aerolineas SET aerolinea_iata = 'KL' WHERE aerolinea_codigo = '074';
UPDATE aerolineas SET aerolinea_iata = 'IB' WHERE aerolinea_codigo = '075';
UPDATE aerolineas SET aerolinea_iata = 'G3' WHERE aerolinea_codigo = '127';
UPDATE aerolineas SET aerolinea_iata = 'AV' WHERE aerolinea_codigo = '134';
UPDATE aerolineas SET aerolinea_iata = 'AM' WHERE aerolinea_codigo = '139';
UPDATE aerolineas SET aerolinea_iata = 'EK' WHERE aerolinea_codigo = '176';
UPDATE aerolineas SET aerolinea_iata = 'LH' WHERE aerolinea_codigo = '220';
UPDATE aerolineas SET aerolinea_iata = 'CM' WHERE aerolinea_codigo = '230';
UPDATE aerolineas SET aerolinea_iata = 'TK' WHERE aerolinea_codigo = '235';
UPDATE aerolineas SET aerolinea_iata = 'VE' WHERE aerolinea_codigo = '246';
UPDATE aerolineas SET aerolinea_iata = 'VB' WHERE aerolinea_codigo = '333';
UPDATE aerolineas SET aerolinea_iata = 'NK' WHERE aerolinea_codigo = '487';
UPDATE aerolineas SET aerolinea_iata = 'H2' WHERE aerolinea_codigo = '605';
UPDATE aerolineas SET aerolinea_iata = 'PU' WHERE aerolinea_codigo = '663';
UPDATE aerolineas SET aerolinea_iata = '9V' WHERE aerolinea_codigo = '742';
UPDATE aerolineas SET aerolinea_iata = 'QL' WHERE aerolinea_codigo = '782';
UPDATE aerolineas SET aerolinea_iata = 'DM' WHERE aerolinea_codigo = '919';
UPDATE aerolineas SET aerolinea_iata = 'WK' WHERE aerolinea_codigo = '945';
UPDATE aerolineas SET aerolinea_iata = 'JA' WHERE aerolinea_codigo = '973';
UPDATE aerolineas SET aerolinea_iata = 'UX' WHERE aerolinea_codigo = '996';
UPDATE aerolineas SET aerolinea_iata = 'P5' WHERE aerolinea_codigo = '000';
-- Quedan en NULL (pendiente del cliente): 035 Latam, 544 Latam, 999 NN Avianca, 067 Turpial, 111 Prueba, 222 Prueba 2

-- 4.2 vuelos del XML FIDS (solo salidas, terminal TU, programados)
CREATE TABLE IF NOT EXISTS vuelos (
  vuelo_id INT AUTO_INCREMENT PRIMARY KEY,
  vuelo_xml_id VARCHAR(20) NOT NULL,
  vuelo_linecode VARCHAR(3) NOT NULL,
  vuelo_numero VARCHAR(10) NOT NULL,
  vuelo_aerolinea_nombre VARCHAR(100),
  vuelo_schedule DATETIME NOT NULL,
  vuelo_actual DATETIME NULL,
  vuelo_tipo CHAR(1) NOT NULL,
  vuelo_destino VARCHAR(150),
  vuelo_destino_codigo VARCHAR(10),
  vuelo_puerta VARCHAR(10),
  vuelo_status VARCHAR(5),
  vuelo_status_texto VARCHAR(50),
  vuelo_actualizado DATETIME NOT NULL,
  UNIQUE KEY uk_vuelo (vuelo_xml_id, vuelo_linecode, vuelo_numero),
  KEY idx_linecode_salida (vuelo_linecode, vuelo_schedule)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4.3 estado de la sincronizacion (una sola fila)
CREATE TABLE IF NOT EXISTS vuelos_sync (
  sync_id INT PRIMARY KEY,
  sync_ultimo_intento DATETIME NULL,
  sync_ultimo_ok DATETIME NULL,
  sync_filas INT NULL,
  sync_error TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT IGNORE INTO vuelos_sync (sync_id) VALUES (1);

-- 4.4 maletas: vuelo asociado (con copia de los datos al registrar)
ALTER TABLE maletas
  ADD maleta_vuelo_id INT NULL,
  ADD maleta_vuelo_numero VARCHAR(15) NULL,
  ADD maleta_vuelo_salida DATETIME NULL,
  ADD maleta_vuelo_destino VARCHAR(150) NULL,
  ADD maleta_vuelo_manual TINYINT(1) NOT NULL DEFAULT 0,
  ADD maleta_minutos_disponibles INT NULL,
  ADD KEY idx_maleta_vuelo (maleta_vuelo_id);

-- 4.5 alertas enviadas (una por vuelo y destino)
CREATE TABLE IF NOT EXISTS alertas_vuelo (
  alerta_id INT AUTO_INCREMENT PRIMARY KEY,
  alerta_vuelo_id INT NOT NULL,
  alerta_destino ENUM('normal','over') NOT NULL,
  alerta_fecha DATETIME NOT NULL,
  alerta_maletas TEXT,
  alerta_resultado VARCHAR(255),
  UNIQUE KEY uk_alerta (alerta_vuelo_id, alerta_destino)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4.6 configuracion (el token del cron va en app/config/config.php)
ALTER TABLE info_pagina ADD info_pagina_alerta_minutos INT NOT NULL DEFAULT 60;
