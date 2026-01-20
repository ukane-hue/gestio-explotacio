<?php
require 'db_config.php';
header('Content-Type: application/json');

// Group by variety to get total stock from harvests
// Note: This matches the request "kg de aquella plantacio" (aggregated by variety)
$sql = "SELECT varietat, SUM(quantitat) as total_kg 
        FROM collites 
        GROUP BY varietat 
        ORDER BY varietat ASC";

$result = $conn->query($sql);

$collites = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $collites[] = [
            'varietat' => $row['varietat'],
            'total_kg' => floatval($row['total_kg'])
        ];
    }
}

echo json_encode($collites);
?>
