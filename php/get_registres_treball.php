<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    $sql = "
        SELECT r.id_registre, r.data, r.hora_inici, r.hora_fi, 
               t.nom, t.cognom, ta.nom_tasca, p.nom_parcela
        FROM registre_treball r
        JOIN treballadors t ON r.id_treballador = t.id_treballador
        JOIN tasques ta ON r.id_tasca = ta.id_tasca
        LEFT JOIN parceles p ON r.id_parcela = p.id_parcela
        ORDER BY r.data DESC, r.hora_inici DESC
    ";
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    json_out(true, ['data' => $data]);
} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
?>
