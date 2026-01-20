<?php
require 'db_config.php';

// 1. Create productes table if not exists
$sql_create = "CREATE TABLE IF NOT EXISTS productes (
    id_producte INT AUTO_INCREMENT PRIMARY KEY,
    nom_comercial VARCHAR(255) NOT NULL UNIQUE,
    tipus ENUM('fitosanitari', 'fertilitzant', 'biologic') DEFAULT 'fitosanitari',
    stock_actual DECIMAL(10,2) DEFAULT 0.00,
    unitat_stock VARCHAR(20) DEFAULT 'kg',
    preu_unitari DECIMAL(10,2) DEFAULT 0.00
)";

if ($conn->query($sql_create) === TRUE) {
    echo "Table 'productes' created or exists.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// 2. Populate productes from tractaments.producte
$sql_populate = "INSERT IGNORE INTO productes (nom_comercial) 
                 SELECT DISTINCT producte FROM tractaments 
                 WHERE producte IS NOT NULL AND producte != ''";

if ($conn->query($sql_populate) === TRUE) {
    echo "Populated 'productes' from 'tractaments'. Rows affected: " . $conn->affected_rows . "<br>";
} else {
    echo "Error populating table: " . $conn->error . "<br>";
}
?>
