<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

try {
    $pdo = db();

    $input = json_decode(file_get_contents('php://input'), true);
    $id_parcela = $input['id_parcela'] ?? null;

    if (!$id_parcela) {
        echo json_encode(['ok' => false, 'error' => 'ID de parcel·la no especificat']);
        exit;
    }

    // Query to get workers and their total hours for the parcel
    $sql = "SELECT 
                t.nom, 
                t.cognom, 
                SUM(TIME_TO_SEC(TIMEDIFF(rt.hora_fi, rt.hora_inici))) / 3600 AS total_hores
            FROM registre_treball rt
            JOIN treballadors t ON rt.id_treballador = t.id_treballador
            WHERE rt.id_parcela = ?
              AND rt.hora_fi IS NOT NULL AND rt.hora_inici IS NOT NULL
            GROUP BY t.id_treballador, t.nom, t.cognom";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_parcela]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'data' => $data]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>
