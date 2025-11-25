-- Gestió Explotació — Esquema complet MySQL
-- Executa aquest script amb un usuari que tingui permisos de creació.


CREATE DATABASE gestio_explotacio1 CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE gestio_explotacio1;

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

-- 3. Taula de PERSONAL
-- Necessària per assignar responsables i controlar el carnet fitosanitari (PDF 05)
CREATE TABLE IF NOT EXISTS personal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    nif VARCHAR(20),
    carrec VARCHAR(50),
    te_carnet_fitosanitari BOOLEAN DEFAULT 0,
    actiu BOOLEAN DEFAULT 1
    
);

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


ALTER TABLE personal
ADD COLUMN telefon VARCHAR(20) AFTER nif,
ADD COLUMN email VARCHAR(100) AFTER telefon,
ADD COLUMN nss VARCHAR(20) AFTER email, -- Número de la Seguretat Social
ADD COLUMN tipus_contracte ENUM('indefinit', 'temporal', 'fix_discontinu', 'practiques', 'altres') DEFAULT 'temporal',
ADD COLUMN durada_contracte VARCHAR(50); -- Ex: "6 mesos", "Fins final de campanya"

-- 1. Crear taula d'Usuaris
CREATE TABLE usuaris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Guardarem el hash, no la contrasenya plana
    nom VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Afegir columna user_id a les taules existents
-- Això vincula cada dada a un usuari
ALTER TABLE parceles ADD COLUMN user_id INT NOT NULL;
ALTER TABLE tractaments ADD COLUMN user_id INT NOT NULL;
ALTER TABLE collites ADD COLUMN user_id INT NOT NULL;
ALTER TABLE personal ADD COLUMN user_id INT NOT NULL;

-- 3. Crear les relacions (Foreign Keys)
ALTER TABLE parceles ADD CONSTRAINT fk_parceles_user FOREIGN KEY (user_id) REFERENCES usuaris(id) ON DELETE CASCADE;
ALTER TABLE tractaments ADD CONSTRAINT fk_tractaments_user FOREIGN KEY (user_id) REFERENCES usuaris(id) ON DELETE CASCADE;
ALTER TABLE collites ADD CONSTRAINT fk_collites_user FOREIGN KEY (user_id) REFERENCES usuaris(id) ON DELETE CASCADE;
ALTER TABLE personal ADD CONSTRAINT fk_personal_user FOREIGN KEY (user_id) REFERENCES usuaris(id) ON DELETE CASCADE;

-- 1. CREAR LA TAULA D'USUARIS (Si no existeix)
CREATE TABLE IF NOT EXISTS usuaris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- 3. VINCULAR LES DADES ALS USUARIS (Afegir user_id)
-- Afegeix la columna user_id a 'personal'
ALTER TABLE personal ADD COLUMN user_id INT NOT NULL;
ALTER TABLE personal ADD CONSTRAINT fk_personal_user FOREIGN KEY (user_id) REFERENCES usuaris(id) ON DELETE CASCADE;

-- Afegeix la columna user_id a 'parceles'
ALTER TABLE parceles ADD COLUMN user_id INT NOT NULL;
ALTER TABLE parceles ADD CONSTRAINT fk_parceles_user FOREIGN KEY (user_id) REFERENCES usuaris(id) ON DELETE CASCADE;

-- Afegeix la columna user_id a 'tractaments'
ALTER TABLE tractaments ADD COLUMN user_id INT NOT NULL;
ALTER TABLE tractaments ADD CONSTRAINT fk_tractaments_user FOREIGN KEY (user_id) REFERENCES usuaris(id) ON DELETE CASCADE;

-- Afegeix la columna user_id a 'collites'
ALTER TABLE collites ADD COLUMN user_id INT NOT NULL;
ALTER TABLE collites ADD CONSTRAINT fk_collites_user FOREIGN KEY (user_id) REFERENCES usuaris(id) ON DELETE CASCADE;