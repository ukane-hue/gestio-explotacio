<?php
require 'db_config.php';
$res = $conn->query("SELECT * FROM productes");
echo "Productes count: " . $res->num_rows . "<br>";
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id_producte'] . " Nom: " . $row['nom_comercial'] . "<br>";
}
?>
