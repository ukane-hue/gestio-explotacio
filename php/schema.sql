-- Gestió Explotació — Esquema complet MySQL
-- Executa aquest script amb un usuari que tingui permisos de creació.

DROP DATABASE IF EXISTS gestio_explotacio;
CREATE DATABASE gestio_explotacio CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE gestio_explotacio;

-- Taula de parcel·les
CREATE TABLE parceles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(120) NOT NULL,
  superficie DECIMAL(10,2) NOT NULL,
  cultiu ENUM('cirerer','presseguer','perera','pomera') NOT NULL,
  varietat VARCHAR(120) DEFAULT NULL,
  geojson JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Taula de collites (harvests)
CREATE TABLE collites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parcel_id INT NULL,
  data DATE NOT NULL,
  varietat VARCHAR(120) NOT NULL,
  quantitat DECIMAL(10,2) DEFAULT NULL,
  equip VARCHAR(200) DEFAULT NULL,
  observacions TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_collites_parcela FOREIGN KEY (parcel_id)
    REFERENCES parceles(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Taula de tractaments (treatments)
CREATE TABLE tractaments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parcel_id INT NULL,
  data DATE NOT NULL,
  producte VARCHAR(200) NOT NULL,
  quantitat DECIMAL(10,2) DEFAULT NULL,
  observacions TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tractaments_parcela FOREIGN KEY (parcel_id)
    REFERENCES parceles(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Índexos útils
CREATE INDEX idx_parceles_nom ON parceles(nom);
CREATE INDEX idx_collites_data ON collites(data);
CREATE INDEX idx_tractaments_data ON tractaments(data);

-- Dades de prova mínimes
INSERT INTO parceles (nom, superficie, cultiu, varietat) VALUES
('Parcela A', 1.20, 'pomera',  'Fuji'),
('Parcela B', 0.85, 'perera',  'Conference'),
('Parcela C', 2.10, 'cirerer', 'Burlat');

INSERT INTO collites (parcel_id, data, varietat, quantitat, equip, observacions) VALUES
(1, '2025-05-10', 'Fuji', 120.5, 'Equip 1', 'Sense incidències'),
(2, '2025-05-11', 'Conference', 75.0, 'Equip 2', 'Molt bona qualitat');

INSERT INTO tractaments (parcel_id, data, producte, quantitat, observacions) VALUES
(1, '2025-04-20', 'Fungicida X', 12.5, 'Aplicat després de pluja'),
(3, '2025-04-25', 'Insecticida Y', 8.0, 'Plaga lleu a la vora');
