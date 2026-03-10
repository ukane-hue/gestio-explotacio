<?php
require_once __DIR__ . '/config.php';

try {
  $in = get_json_input();
  
  $nom = trim($in['nom'] ?? '');
  $nif = trim($in['nif'] ?? '');
  $carrec = trim($in['carrec'] ?? '');
  // Convertim el true/false de JS a 1/0 de SQL
  $carnet = !empty($in['carnet']) ? 1 : 0; 

  if ($nom === '') {
    json_out(false, ['error' => 'El nom és obligatori.']);
  }

  $pdo = db();
  $stmt = $pdo->prepare("INSERT INTO personal (nom, nif, carrec, te_carnet_fitosanitari) VALUES (?, ?, ?, ?)");
  $stmt->execute([$nom, $nif, $carrec, $carnet]);

  json_out(true, ['id' => $pdo->lastInsertId()]);

} catch (Throwable $e) {
  json_out(false, ['error' => $e->getMessage()]);
}
?>