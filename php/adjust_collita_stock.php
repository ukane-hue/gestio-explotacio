<?php
require 'db_config.php';
session_start();
$user_id = $_SESSION['user_id'] ?? 1; // Fallback to 1 if no session (should verify login)
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['varietat']) && isset($data['new_total'])) {
    $varietat = $conn->real_escape_string($data['varietat']);
    $new_total = floatval($data['new_total']);

    // 1. Get current total
    $sql_sum = "SELECT SUM(quantitat) as current_total FROM collites WHERE varietat = '$varietat'";
    $result = $conn->query($sql_sum);
    $row = $result->fetch_assoc();
    $current_total = $row ? floatval($row['current_total']) : 0;

    $diff = $new_total - $current_total;

    if (abs($diff) < 0.001) {
        echo json_encode(['success' => true, 'message' => 'No change needed']);
        exit;
    }

    // 2. Find a parcel_id to associate (optional, best effort)
    // Try to find the latest parcel that produced this variety
    $parcel_id = "NULL";
    $sql_parcel = "SELECT parcel_id FROM collites WHERE varietat = '$varietat' AND parcel_id IS NOT NULL ORDER BY data DESC LIMIT 1";
    $res_parcel = $conn->query($sql_parcel);
    if ($res_parcel && $res_parcel->num_rows > 0) {
        $p_row = $res_parcel->fetch_assoc();
        $parcel_id = $p_row['parcel_id'];
    }

    // 3. Insert adjustment
    $obs = "Ajust Manual Inventari";
    $sql_insert = "INSERT INTO collites (data, varietat, quantitat, observacions, parcel_id, created_at, user_id) 
                   VALUES (CURDATE(), '$varietat', $diff, '$obs', $parcel_id, NOW(), $user_id)";

    if ($conn->query($sql_insert) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
}

$conn->close();
?>
