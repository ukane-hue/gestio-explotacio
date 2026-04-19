-- Dades de prova per a gestio_explotacio
-- NOTA IMPORTANT: No inclou l'usuari admin!
-- Has de crear l'admin executant el fitxer 'php/create_admin.php' en el terminal o desastant-lo per web.

USE gestio_explotacio;

-- 1. Espècies
INSERT INTO especies (nom_cientific, nom_comu) VALUES
('Prunus avium', 'Cirerer'),
('Prunus persica', 'Presseguer'),
('Pyrus communis', 'Perera'),
('Malus domestica', 'Pomera');

-- 2. Varietats
INSERT INTO varietats (id_especie, nom_varietat, necessitats_hidriques, hores_fred, productivitat_esperada) VALUES
(1, 'Burlat', 600.00, 400, 15000.00),
(1, 'Lapins', 650.00, 500, 18000.00),
(2, 'Roig d''Albesa', 700.00, 600, 25000.00),
(3, 'Conference', 800.00, 800, 30000.00),
(4, 'Fuji', 750.00, 900, 40000.00);

-- 3. Parcel·les (Assignades a l'usuari admin per defecte, id_propietari = 1)
INSERT INTO parceles (nom_parcela, superficie, tipus_sol, ph, materia_organica, id_propietari) VALUES
('Finca Vella', 2.50, 'Franco-argilós', 7.20, 2.50, 1),
('Camp Nou', 1.85, 'Sorrenc', 6.80, 1.80, 1),
('Hort del Riu', 3.20, 'Llimós', 7.50, 3.10, 1);

-- 4. Productes
INSERT INTO productes (nom_comercial, materia_activa, tipus, concentracio, unitat_stock, stock_actual, preu_unitari) VALUES
('Cobre Azul', 'Oxiclorur de coure', 'fitosanitari', '50%', 'kg', 25.00, 12.50),
('Abonament NPK', 'Nitrogen-Fòsfor-Potassi', 'fertilitzant', '15-15-15', 'kg', 500.00, 2.10),
('Bacillus T', 'Bacillus thuringiensis', 'biologic', '32000 UI/mg', 'kg', 10.00, 35.00);

-- 5. Treballadors
INSERT INTO treballadors (nom, cognom, dni, telefon, tipus_contracte, categoria, data_inici) VALUES
('Joan', 'Garcia', '12345678A', '600123456', 'Fixe', 'Encarregat', '2023-01-15'),
('Maria', 'López', '87654321B', '600654321', 'Temporal', 'Peó', '2025-03-01'),
('Pere', 'Martínez', '45678912C', '600111222', 'Temporal', 'Tractorista', '2024-05-10');

-- 6. Maquinària
INSERT INTO maquinaria (nom, tipus, matricula, data_compra, estat) VALUES
('Tractor John Deere', 'Tractor', 'E1234BBD', '2020-05-20', 'actiu'),
('Atomitzador 2000L', 'Atomitzador', 'E5678CCC', '2021-03-15', 'actiu'),
('Tractor New Holland', 'Tractor', 'E9999XYZ', '2019-11-10', 'reparacio');

-- 7. Tasques
INSERT INTO tasques (nom_tasca, descripcio) VALUES
('Poda', 'Poda d''hivern dels arbres fruiters'),
('Tractament foliar', 'Aplicació de fitosanitaris amb atomitzador'),
('Collita', 'Recollida manual de la fruita'),
('Abonat fons', 'Aplicació d''adob granular al sòl');

-- 8. Sectors (Opcional, dividint la Finca Vella)
INSERT INTO sectors (id_parcela, nom_sector) VALUES
(1, 'Sector Nord'),
(1, 'Sector Sud'),
(2, 'Sector Únic');

-- 9. Plantacions (Unint parcel·les i varietats)
INSERT INTO plantacions (id_parcela, id_sector, id_varietat, data_plantacio, marc_plantacio, nombre_arbres, sistema_formacio) VALUES
(1, 1, 1, '2015-02-15', '4x3', 800, 'vas'),
(1, 2, 2, '2016-01-20', '4x3', 750, 'vas'),
(2, 3, 4, '2018-03-10', '3.5x1.5', 1500, 'eix_central');
