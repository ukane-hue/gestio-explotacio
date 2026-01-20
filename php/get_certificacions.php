<?php
require_once __DIR__ . '/config.php';

try {
    $id_treballador = $_GET['id_treballador'] ?? null;
    
    $pdo = db();
    
    if ($id_treballador) {
        $stmt = $pdo->prepare("
            SELECT c.*, t.nom, t.cognom 
            FROM certificacions c
            JOIN treballadors t ON c.id_treballador = t.id_treballador
            WHERE c.id_treballador = ?
            ORDER BY c.data_caducitat ASC
        ");
        $stmt->execute([$id_treballador]);
    } else {
        // Si no s'especifica treballador, potser les volem totes o error?
        // Retornem totes per ara, útil per llistats generals
        $stmt = $pdo->query("
            SELECT c.*, t.nom, t.cognom 
            FROM certificacions c
            JOIN treballadors t ON c.id_treballador = t.id_treballador
            ORDER BY t.cognom, c.data_caducitat ASC
        ");
    }
    
    $certificacions = $stmt->fetchAll();
    json_out(true, ['certificacions' => $certificacions]);

} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
