<?php
require 'db_config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['nom_comercial']) && isset($data['tipus'])) {
    $nom = $conn->real_escape_string($data['nom_comercial']);
    $tipus = $conn->real_escape_string($data['tipus']);
    $stock = isset($data['stock_actual']) ? floatval($data['stock_actual']) : 0;
    $preu = isset($data['preu_unitari']) ? floatval($data['preu_unitari']) : 0;
    $unitat = isset($data['unitat_stock']) ? $conn->real_escape_string($data['unitat_stock']) : 'kg';

    $sql = "INSERT INTO productes (nom_comercial, tipus, stock_actual, preu_unitari, unitat_stock) VALUES ('$nom', '$tipus', $stock, $preu, '$unitat')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
}

$conn->close();
?>
