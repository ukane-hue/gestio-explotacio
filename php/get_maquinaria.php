<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    $stmt = $pdo->query("SELECT * FROM maquinaria ORDER BY nom");
    $maquinaria = $stmt->fetchAll();
    json_out(true, ['maquinaria' => $maquinaria]);
} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
