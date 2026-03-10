<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    $stmt = $pdo->query("SELECT * FROM treballadors ORDER BY cognom, nom");
    $treballadors = $stmt->fetchAll();
    
    json_out(true, ['treballadors' => $treballadors]);
} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
