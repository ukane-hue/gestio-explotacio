<?php
// Manual test script to verify fixes
require 'db_config.php';
session_start();
$_SESSION['user_id'] = 1; // Simulate logic

echo "--- Testing Product Creation ---\n";
// Simulate create_product.php logic
$nom = 'TestProdManual';
$tipus = 'fitosanitari';
$stock = 10;
$preu = 5;
$unitat = 'kg';

$sql = "INSERT INTO productes (nom_comercial, tipus, stock_actual, preu_unitari, unitat_stock) VALUES ('$nom', '$tipus', $stock, $preu, '$unitat')";
if ($conn->query($sql) === TRUE) {
    echo "Producte OK: ID " . $conn->insert_id . "\n";
    $conn->query("DELETE FROM productes WHERE id_producte = " . $conn->insert_id); // Cleanup
} else {
    echo "Producte FAIL: " . $conn->error . "\n";
}

echo "--- Testing Collita Creation ---\n";
// Simulate create_collita.php logic
$parcel_id = 'NULL';
$varietat = 'TestVar';
$quantitat = 50;
$data_collita = date('Y-m-d');
$user_id = 1;
$obs = 'Test';

// Need to find a valid parcel or just use NULL for now if allowed?
// Parcel ID is nullable but we might want to test with one.
// Let's use NULL for simplicity as describe showed it's nullable.
// UPDATE: User confirmed "user_id" constraint was the issue.

$sql_insert = "INSERT INTO collites (parcel_id, data, varietat, quantitat, observacions, created_at, user_id) 
               VALUES ($parcel_id, '$data_collita', '$varietat', $quantitat, '$obs', NOW(), $user_id)";

if ($conn->query($sql_insert) === TRUE) {
    echo "Collita OK: ID " . $conn->insert_id . "\n";
    $conn->query("DELETE FROM collites WHERE id = " . $conn->insert_id); // Cleanup
} else {
    echo "Collita FAIL: " . $conn->error . "\n";
}

echo "Done.\n";
?>
