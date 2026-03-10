<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

try {
    $pdo = db();

    // Obtenir plantacions (parcel·la + varietat) per al dropdown de collites
    $sql = "
        SELECT 
            pl.id_plantacio AS id,
            p.nom_parcela,
            v.nom_varietat
        FROM plantacions pl
        JOIN parceles p ON pl.id_parcela = p.id_parcela
        JOIN varietats v ON pl.id_varietat = v.id_varietat
        ORDER BY p.nom_parcela, v.nom_varietat
    ";

    $stmt = $pdo->query($sql);
    $parceles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = array_map(function($row) {
        return [
            'id'       => $row['id'],
            'nom'      => $row['nom_parcela'],
            'varietat' => $row['nom_varietat']
        ];
    }, $parceles);

    echo json_encode($data);

} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
