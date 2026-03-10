<?php
require 'db_config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id_producte'])) {
    $id = intval($data['id_producte']);

    $stmt = $conn->prepare("DELETE FROM productes WHERE id_producte = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'ok' => true]);
    } else {
        echo json_encode(['success' => false, 'ok' => false, 'error' => $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'ok' => false, 'error' => 'Missing id_producte']);
}

$conn->close();
?>
