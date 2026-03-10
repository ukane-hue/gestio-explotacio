<?php
require 'config.php';
header('Content-Type: application/json');

try {
    $data = get_json_input();

    if (isset($data['nom_comercial']) && isset($data['tipus'])) {
        $nom = trim($data['nom_comercial']);
        $tipus = trim($data['tipus']);
        $stock = isset($data['stock_actual']) ? floatval($data['stock_actual']) : 0;
        $preu = isset($data['preu_unitari']) ? floatval($data['preu_unitari']) : 0;
        $unitat = isset($data['unitat_stock']) ? trim($data['unitat_stock']) : 'kg';

        if (empty($nom)) {
            json_out(false, ['error' => 'El nom és obligatori']);
        }

        $pdo = db();
        $sql = "INSERT INTO productes (nom_comercial, tipus, stock_actual, preu_unitari, unitat_stock) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$nom, $tipus, $stock, $preu, $unitat])) {
            json_out(true, ['id' => $pdo->lastInsertId()]);
        } else {
            json_out(false, ['error' => 'Error executant la consulta']);
        }
    } else {
        json_out(false, ['error' => 'Falten camps obligatoris']);
    }

} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
?>
