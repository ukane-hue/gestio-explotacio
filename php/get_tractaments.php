<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    // Join with plantacions -> parceles to get names
    $sql = "
        SELECT t.id_tractament, t.data_aplicacio, t.metode_aplicacio, p.nom_parcela, t.observacions
        FROM tractaments t
        JOIN plantacions pl ON t.id_plantacio = pl.id_plantacio
        LEFT JOIN parceles p ON pl.id_parcela = p.id_parcela
        ORDER BY t.data_aplicacio DESC
    ";
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    json_out(true, ['data' => $data]);
} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
?>
