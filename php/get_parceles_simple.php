<?php
require 'db_config.php';
header('Content-Type: application/json');

$sql = "SELECT id, nom, varietat FROM parceles ORDER BY nom ASC";
$result = $conn->query($sql);

$parceles = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $parceles[] = [
            'id' => $row['id'],
            'nom' => $row['nom'],
            'varietat' => $row['varietat']
        ];
    }
}

echo json_encode($parceles);

$conn->close();
?>
