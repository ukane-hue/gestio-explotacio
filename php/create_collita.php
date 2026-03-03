<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// parcel_id here is id_plantacio (from get_parceles_simple.php)
if (!isset($data['parcel_id']) || !isset($data['quantitat'])) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

try {
    $pdo = db();
    $id_plantacio = intval($data['parcel_id']);
    $quantitat    = floatval($data['quantitat']);
    $data_inici   = !empty($data['data']) ? $data['data'] . ' 00:00:00' : date('Y-m-d H:i:s');

    // Get id_varietat from plantació
    $stmt = $pdo->prepare("SELECT id_varietat FROM plantacions WHERE id_plantacio = ?");
    $stmt->execute([$id_plantacio]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['success' => false, 'error' => 'Plantació no trobada']);
        exit;
    }

    $id_varietat = $row['id_varietat'];

    // Generate a simple lot_id
    $lot_id = 'LOT-INV-' . date('YmdHis') . '-' . $id_plantacio;

    // Insert into collites
    $stmt2 = $pdo->prepare("
        INSERT INTO collites (id_plantacio, data_inici, id_varietat, quantitat_recoltada, unitat, lot_id)
        VALUES (?, ?, ?, ?, 'kg', ?)
    ");
    $stmt2->execute([$id_plantacio, $data_inici, $id_varietat, $quantitat, $lot_id]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'lot_id' => $lot_id]);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
