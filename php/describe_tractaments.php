<?php
require 'db_config.php';
$result = $conn->query("DESCRIBE tractaments");
while($row = $result->fetch_array()) {
    echo $row[0] . " - " . $row[1] . "<br>";
}
?>
