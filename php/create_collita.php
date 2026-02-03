<?php
require 'db_config.php';
session_start();
$user_id = $_SESSION['user_id'] ?? 1;
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// Receives: parcel_id, quantitat, date (optional, default today)
if (isset($data['parcel_id']) && isset($data['quantitat'])) {
    $parcel_id = intval($data['parcel_id']);
    $quantitat = floatval($data['quantitat']);
    $data_collita = isset($data['data']) ? $conn->real_escape_string($data['data']) : date('Y-m-d');

    // Fetch variety from parceles table
    $sql_var = "SELECT varietat FROM parceles WHERE id = $parcel_id";
    $res_var = $conn->query($sql_var);
    
    if ($res_var && $res_var->num_rows > 0) {
        $row_var = $res_var->fetch_assoc();
        $varietat = $conn->real_escape_string($row_var['varietat']);

        // Insert into collites
        $obs = "Entrada Manual Inventari";
        $sql_insert = "INSERT INTO collites (parcel_id, data, varietat, quantitat, observacions, created_at, user_id) 
                       VALUES ($parcel_id, '$data_collita', '$varietat', $quantitat, '$obs', NOW(), $user_id)";
        
        if ($conn->query($sql_insert) === TRUE) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Parcel not found']);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
}

$conn->close();
?>
