<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    $stmt = $pdo->query("SELECT * FROM tasques ORDER BY nom_tasca");
    $tasques = $stmt->fetchAll();
    json_out(true, ['tasques' => $tasques]);
} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
