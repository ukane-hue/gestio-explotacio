<?php
require 'db_config.php';
$result = $conn->query("DESCRIBE collites");
while($row = $result->fetch_array()) {
    echo $row[0] . " - " . $row[1] . "<br>";
}
?>
