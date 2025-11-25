<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    // Seleccionem ID i NOM per omplir els desplegables (selects)
    $stmt = $pdo->query("SELECT id, nom FROM parceles ORDER BY nom ASC");
    $parceles = $stmt->fetchAll();
    
    json_out(true, ['data' => $parceles]);
} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
?>