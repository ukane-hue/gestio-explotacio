<?php
require 'db_config.php';
$res = $conn->query("SELECT COUNT(*) FROM tractaments");
echo "Tractaments count: " . $res->fetch_row()[0] . "<br>";
$res2 = $conn->query("SELECT producte FROM tractaments LIMIT 5");
while($row = $res2->fetch_assoc()) {
    echo "Prod: " . $row['producte'] . "<br>";
}
?>
