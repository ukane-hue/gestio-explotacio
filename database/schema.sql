-- ========================================
-- BASE DE DADES: Gestió Explotació Fruitera
-- Total taules: 16
-- Idioma: Català
-- ========================================
CREATE DATABASE IF NOT EXISTS gestio_explotacio
CHARACTER SET utf8mb4 COLLATE utf8mb4_catalan_ci;
USE gestio_explotacio;

-- 1. usuaris
CREATE TABLE usuaris (
    id_usuari INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    cognoms VARCHAR(255),
    email VARCHAR(255) UNIQUE NOT NULL,
    contrasenya_hash VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'gestor', 'operari', 'recoltador') NOT NULL,
    telefon VARCHAR(20),
    actiu BOOLEAN DEFAULT TRUE,
    creat_el TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. especies
CREATE TABLE especies (
    id_especie INT AUTO_INCREMENT PRIMARY KEY,
    nom_cientific VARCHAR(255) NOT NULL,
    nom_comu VARCHAR(255) NOT NULL
);

-- 3. varietats
CREATE TABLE varietats (
    id_varietat INT AUTO_INCREMENT PRIMARY KEY,
    id_especie INT NOT NULL,
    nom_varietat VARCHAR(255) NOT NULL,
    necessitats_hidriques DECIMAL(6,2),
    hores_fred INT,
    resistencia_malalties TEXT,
    cicle_vegetatiu TEXT,
    requeriments_pollinitzacio TEXT,
    productivitat_esperada DECIMAL(8,2),
    qualitats_organoleptiques TEXT,
    foto_url VARCHAR(512),
    FOREIGN KEY (id_especie) REFERENCES especies(id_especie) ON DELETE CASCADE
);

-- 4. parceles
CREATE TABLE parceles (
    id_parcela INT AUTO_INCREMENT PRIMARY KEY,
    nom_parcela VARCHAR(255) NOT NULL,
    superficie DECIMAL(10,2) NOT NULL,
    tipus_sol VARCHAR(100),
    ph DECIMAL(4,2),
    materia_organica DECIMAL(5,2),
    pendent DECIMAL(5,2),
    orientacio VARCHAR(50),
    infraestructures TEXT,
    documentacio TEXT,
    perimetre_geo JSON,
    creat_el TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. sectors
CREATE TABLE sectors (
    id_sector INT AUTO_INCREMENT PRIMARY KEY,
    id_parcela INT NOT NULL,
    nom_sector VARCHAR(255),
    geo JSON,
    FOREIGN KEY (id_parcela) REFERENCES parceles(id_parcela) ON DELETE CASCADE
);

-- 6. files
CREATE TABLE files (
    id_fila INT AUTO_INCREMENT PRIMARY KEY,
    id_sector INT NOT NULL,
    numero_fila INT NOT NULL,
    FOREIGN KEY (id_sector) REFERENCES sectors(id_sector) ON DELETE CASCADE
);

-- 7. plantacions
CREATE TABLE plantacions (
    id_plantacio INT AUTO_INCREMENT PRIMARY KEY,
    id_parcela INT,
    id_sector INT,
    id_varietat INT NOT NULL,
    data_plantacio DATE NOT NULL,
    marc_plantacio VARCHAR(50),
    nombre_arbres INT NOT NULL,
    origen_material_vegetal VARCHAR(255),
    sistema_formacio ENUM('vas', 'palmeta', 'eix_central', 'altre'),
    estat_fenologic_actual VARCHAR(100),
    FOREIGN KEY (id_parcela) REFERENCES parceles(id_parcela) ON DELETE SET NULL,
    FOREIGN KEY (id_sector) REFERENCES sectors(id_sector) ON DELETE SET NULL,
    FOREIGN KEY (id_varietat) REFERENCES varietats(id_varietat) ON DELETE CASCADE
);

-- 8. productes
CREATE TABLE productes (
    id_producte INT AUTO_INCREMENT PRIMARY KEY,
    nom_comercial VARCHAR(255) NOT NULL,
    materia_activa VARCHAR(255),
    tipus ENUM('fitosanitari', 'fertilitzant', 'biologic') NOT NULL,
    concentracio VARCHAR(100),
    cultius_autoritzats TEXT,
    dosis_recomanada VARCHAR(100),
    termini_seguretat INT,
    classificacio_toxicologica VARCHAR(100),
    ecotoxicologica VARCHAR(100),
    compatible_ecologic BOOLEAN DEFAULT FALSE,
    registre_oficial VARCHAR(100)
);

-- 9. tractaments
CREATE TABLE tractaments (
    id_tractament INT AUTO_INCREMENT PRIMARY KEY,
    id_producte INT NOT NULL,
    id_plantacio INT NOT NULL,
    data_aplicacio DATETIME NOT NULL,
    quantitat_aplicada DECIMAL(10,3),
    unitat ENUM('L', 'kg', 'g'),
    metode_aplicacio ENUM('fertirrigacio', 'foliar', 'sol', 'aeri', 'trampa'),
    id_operari INT,
    observacions TEXT,
    condicions_ambientals JSON,
    FOREIGN KEY (id_producte) REFERENCES productes(id_producte) ON DELETE RESTRICT,
    FOREIGN KEY (id_plantacio) REFERENCES plantacions(id_plantacio) ON DELETE CASCADE,
    FOREIGN KEY (id_operari) REFERENCES usuaris(id_usuari) ON DELETE SET NULL
);

-- 10. collites
CREATE TABLE collites (
    id_collita INT AUTO_INCREMENT PRIMARY KEY,
    id_plantacio INT NOT NULL,
    data_inici DATETIME NOT NULL,
    data_fi DATETIME,
    id_varietat INT NOT NULL,
    quantitat_recoltada DECIMAL(10,3) NOT NULL,
    unitat ENUM('kg', 'caixes', 'bins') NOT NULL,
    equip_recoltadors JSON,
    condicions_ambientals JSON,
    estat_fenologic VARCHAR(100),
    incidencies TEXT,
    lot_id VARCHAR(100) UNIQUE,
    FOREIGN KEY (id_plantacio) REFERENCES plantacions(id_plantacio) ON DELETE CASCADE,
    FOREIGN KEY (id_varietat) REFERENCES varietats(id_varietat) ON DELETE RESTRICT
);

-- 11. controls_qualitat
CREATE TABLE controls_qualitat (
    id_control INT AUTO_INCREMENT PRIMARY KEY,
    id_collita INT NOT NULL,
    data_control DATE NOT NULL,
    calibre DECIMAL(5,2),
    color VARCHAR(50),
    forma VARCHAR(50),
    fermesa DECIMAL(5,2),
    defectes_visibles TEXT,
    sabor TEXT,
    aroma TEXT,
    textura TEXT,
    percentatge_comercialitzable DECIMAL(5,2),
    motiu_rebuig TEXT,
    id_operari INT,
    FOREIGN KEY (id_collita) REFERENCES collites(id_collita) ON DELETE CASCADE,
    FOREIGN KEY (id_operari) REFERENCES usuaris(id_usuari) ON DELETE SET NULL
);

-- 12. lots
CREATE TABLE lots (
    id_lot VARCHAR(100) PRIMARY KEY,
    id_collita INT NOT NULL,
    data_creacio DATE NOT NULL,
    origen_parcela VARCHAR(255),
    resultat_qualitat JSON,
    tractaments_aplicats JSON,
    transport TEXT,
    client_final VARCHAR(255),
    codi_qr VARCHAR(512),
    FOREIGN KEY (id_collita) REFERENCES collites(id_collita) ON DELETE CASCADE
);

-- 13. observacions_fitosanitaries
CREATE TABLE observacions_fitosanitaries (
    id_observacio INT AUTO_INCREMENT PRIMARY KEY,
    id_plantacio INT NOT NULL,
    data_observacio DATE NOT NULL,
    plaga_o_malaltia VARCHAR(255),
    nivell_incidencia ENUM('baix', 'mitja', 'alt'),
    localitzacio_geo JSON,
    fotos JSON,
    id_operari INT,
    FOREIGN KEY (id_plantacio) REFERENCES plantacions(id_plantacio) ON DELETE CASCADE,
    FOREIGN KEY (id_operari) REFERENCES usuaris(id_usuari) ON DELETE SET NULL
);

-- 14. trampes_monitoratge
CREATE TABLE trampes_monitoratge (
    id_trampa INT AUTO_INCREMENT PRIMARY KEY,
    id_plantacio INT NOT NULL,
    tipus_plaga VARCHAR(100),
    ubicacio_geo POINT, -- Requereix suport espacial
    data_instalacio DATE,
    data_ultima_revisio DATE,
    captures_ultima_revisio INT,
    llindar_intervencio INT,
    estat ENUM('activa', 'inactiva', 'avariada'),
    FOREIGN KEY (id_plantacio) REFERENCES plantacions(id_plantacio) ON DELETE CASCADE
);

-- 15. previsions_collita
CREATE TABLE previsions_collita (
    id_previsio INT AUTO_INCREMENT PRIMARY KEY,
    id_plantacio INT NOT NULL,
    any_campanya YEAR NOT NULL,
    quantitat_prevista DECIMAL(10,3),
    qualitat_prevista ENUM('excel·lent', 'bona', 'regular', 'dolenta'),
    data_previsio DATE NOT NULL,
    font_dades ENUM('historic', 'fenologia', 'meteorologia', 'model'),
    precisio DECIMAL(5,2),
    FOREIGN KEY (id_plantacio) REFERENCES plantacions(id_plantacio) ON DELETE CASCADE
);

-- 16. alertes
CREATE TABLE alertes (
    id_alerta INT AUTO_INCREMENT PRIMARY KEY,
    tipus ENUM('qualitat', 'plaga', 'meteorologia', 'collita', 'tractament'),
    missatge TEXT NOT NULL,
    nivell ENUM('info', 'advertencia', 'critica'),
    data_creacio DATETIME DEFAULT CURRENT_TIMESTAMP,
    vista BOOLEAN DEFAULT FALSE,
    id_usuari_objectiu INT,
    id_collita INT NULL,
    id_tractament INT NULL,
    id_observacio INT NULL,
    FOREIGN KEY (id_usuari_objectiu) REFERENCES usuaris(id_usuari),
    FOREIGN KEY (id_collita) REFERENCES collites(id_collita) ON DELETE SET NULL,
    FOREIGN KEY (id_tractament) REFERENCES tractaments(id_tractament) ON DELETE SET NULL,
    FOREIGN KEY (id_observacio) REFERENCES observacions_fitosanitaries(id_observacio) ON DELETE SET NULL
);

-- Índexs per rendiment
CREATE INDEX idx_parcela_nom ON parceles(nom_parcela);
CREATE INDEX idx_plantacio_data ON plantacions(data_plantacio);
CREATE INDEX idx_collita_data ON collites(data_inici);
CREATE INDEX idx_tractament_data ON tractaments(data_aplicacio);
CREATE INDEX idx_lot ON lots(id_lot);
CREATE INDEX idx_alerta_nivell ON alertes(nivell);

-- Habilitar suport espacial (per trampes_monitoratge)
ALTER TABLE trampes_monitoratge ADD SPATIAL INDEX(ubicacio_geo);

-- ========================================
-- VISTES ÚTILES
-- ========================================

-- Rendiment per hectàrea i any
CREATE VIEW vista_rendiment AS
SELECT 
    p.id_parcela,
    p.nom_parcela,
    YEAR(c.data_inici) AS any,
    SUM(c.quantitat_recoltada) / p.superficie AS kg_per_hectarea
FROM collites c
JOIN plantacions pl ON c.id_plantacio = pl.id_plantacio
JOIN parceles p ON COALESCE(pl.id_parcela, (SELECT s.id_parcela FROM sectors s WHERE s.id_sector = pl.id_sector)) = p.id_parcela
GROUP BY p.id_parcela, YEAR(c.data_inici);

-- Traçabilitat completa
CREATE VIEW vista_tracabilitat AS
SELECT 
    l.id_lot,
    c.data_inici AS data_collita,
    p.nom_parcela,
    v.nom_varietat,
    cq.percentatge_comercialitzable,
    l.client_final
FROM lots l
JOIN collites c ON l.id_collita = c.id_collita
JOIN controls_qualitat cq ON cq.id_collita = c.id_collita
JOIN plantacions pl ON c.id_plantacio = pl.id_plantacio
JOIN varietats v ON pl.id_varietat = v.id_varietat
JOIN parceles p ON COALESCE(pl.id_parcela, (SELECT s.id_parcela FROM sectors s WHERE s.id_sector = pl.id_sector)) = p.id_parcela;
