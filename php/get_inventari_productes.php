<?php
require 'db_config.php';
header('Content-Type: application/json');

$sql = "SELECT id_producte, nom_comercial, tipus, stock_actual, unitat_stock, preu_unitari FROM productes ORDER BY nom_comercial ASC";
$result = $conn->query($sql);

$productes = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $productes[] = $row;
    }
}

echo json_encode($productes);
?>
