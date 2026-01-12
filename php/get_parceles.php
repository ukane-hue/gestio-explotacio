<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    
    // Obtenir usuari actual
    if (session_status() === PHP_SESSION_NONE) session_start();
    $id_usuari = $_SESSION['user_id'] ?? null;
    $rol = $_SESSION['user_role'] ?? '';

    $where = "";
    $params = [];

    // Si no és admin/gestor, filtrar per propietari
    if ($rol !== 'admin' && $rol !== 'gestor') {
        if (!$id_usuari) {
            json_out(true, ['data' => []]); // No login, no data
            exit;
        }
        $where = "WHERE p.id_propietari = ?";
        $params[] = $id_usuari;
    }

    // Obtenim parcel·les amb informació de la plantació (cultiu/varietat)
    // Assumim una plantació activa per parcel·la (o la més recent)
    $sql = "
        SELECT 
            p.id_parcela, 
            p.nom_parcela, 
            p.superficie, 
            p.perimetre_geo,
            pl.id_plantacio,
            e.nom_comu as cultiu,
            v.nom_varietat as varietat
        FROM parceles p
        LEFT JOIN plantacions pl ON p.id_parcela = pl.id_parcela
        LEFT JOIN varietats v ON pl.id_varietat = v.id_varietat
        LEFT JOIN especies e ON v.id_especie = e.id_especie
        $where
        ORDER BY p.nom_parcela ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $parceles = $stmt->fetchAll();
    
    // Decodificar GeoJSON per al frontend si cal, o enviar-ho com string
    // El frontend espera 'geojson' en el format que Leaflet pugui llegir.
    // A la BD està com string JSON.
    
    foreach ($parceles as &$p) {
        if ($p['perimetre_geo']) {
            $p['geojson'] = json_decode($p['perimetre_geo']);
        } else {
            $p['geojson'] = null;
        }
    }
    
    json_out(true, ['data' => $parceles]);

} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}