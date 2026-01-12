<?php
require_once __DIR__ . '/config.php';

try {
  $in = get_json_input();
  
  $id_plantacio = isset($in['id_plantacio']) ? intval($in['id_plantacio']) : null;
  $data_aplicacio = trim($in['data'] ?? '');
  $observacions = trim($in['observacions'] ?? '');
  $productes = $in['productes'] ?? []; // Array de { nom, quantitat, unitat }

  if (!$id_plantacio || !$data_aplicacio || empty($productes)) {
    json_out(false, ['error' => 'Falten camps obligatoris (plantació, data o productes).']);
  }

  $pdo = db();
  $pdo->beginTransaction();

  // 1. Inserir Tractament (Capçalera)
  $stmt = $pdo->prepare(
      "INSERT INTO tractaments (id_plantacio, data_aplicacio, observacions) 
       VALUES (?, ?, ?)"
  );
  $stmt->execute([$id_plantacio, $data_aplicacio, $observacions ?: null]);
  $id_tractament = $pdo->lastInsertId();

  // 2. Processar Productes
  foreach ($productes as $prod) {
      $nom_producte = trim($prod['nom'] ?? '');
      $quantitat = floatval($prod['quantitat'] ?? 0);
      $unitat = $prod['unitat'] ?? 'L';

      if (!$nom_producte || $quantitat <= 0) continue;

      // Buscar o crear producte
      $stmt_prod = $pdo->prepare("SELECT id_producte FROM productes WHERE nom_comercial = ?");
      $stmt_prod->execute([$nom_producte]);
      $id_producte = $stmt_prod->fetchColumn();
      
      if (!$id_producte) {
          $stmt_new = $pdo->prepare("INSERT INTO productes (nom_comercial, tipus) VALUES (?, 'fitosanitari')");
          $stmt_new->execute([$nom_producte]);
          $id_producte = $pdo->lastInsertId();
      }

      // Inserir relació
      $stmt_rel = $pdo->prepare(
          "INSERT INTO tractaments_productes (id_tractament, id_producte, quantitat_aplicada, unitat)
           VALUES (?, ?, ?, ?)"
      );
      $stmt_rel->execute([$id_tractament, $id_producte, $quantitat, $unitat]);
  }

  // 3. Processar Maquinària (M:N)
  $maquinaria = $in['maquinaria'] ?? []; // Array d'IDs
  if (is_array($maquinaria) && count($maquinaria) > 0) {
      $stmt_maq = $pdo->prepare("INSERT INTO tractaments_maquinaria (id_tractament, id_maquina) VALUES (?, ?)");
      foreach ($maquinaria as $id_maquina) {
          $stmt_maq->execute([$id_tractament, $id_maquina]);
      }
  }
  
  $pdo->commit();
  json_out(true, ['id' => $id_tractament]);

} catch (Throwable $e) {
  if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
  json_out(false, ['error' => $e->getMessage()]);
}
