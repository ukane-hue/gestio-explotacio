<?php
require 'db_config.php';
$result = $conn->query("SHOW TABLES");
if ($result->num_rows > 0) {
    while($row = $result->fetch_array()) {
        echo $row[0] . "<br>";
    }
} else {
    echo "No tables found in " . $dbname;
}
?>
