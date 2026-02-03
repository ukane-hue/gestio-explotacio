<?php
require_once __DIR__ . '/config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Mètode no permès");
    }

    $input = get_json_input();
    $id = $input['id'] ?? null;

    if (!$id) {
        throw new Exception("Falta ID");
    }

    $pdo = db();
    $stmt = $pdo->prepare("DELETE FROM tractaments WHERE id_tractament = ?");
    $stmt->execute([$id]);

    json_out(true, ['message' => 'Eliminat correctament']);
} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
?>
