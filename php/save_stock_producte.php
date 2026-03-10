<?php
require 'db_config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id_producte']) && isset($data['stock'])) {
    $id = intval($data['id_producte']);
    $stock = floatval($data['stock']);
    $price = isset($data['price']) ? floatval($data['price']) : 0;

    $stmt = $conn->prepare("UPDATE productes SET stock_actual = ?, preu_unitari = ? WHERE id_producte = ?");
    $stmt->bind_param("ddi", $stock, $price, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}
?>
