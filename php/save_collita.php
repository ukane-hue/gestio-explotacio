<?php
require_once __DIR__ . '/config.php';

try {
  $in = get_json_input();
  
  // Camps obligatoris
  $id_plantacio = isset($in['id_plantacio']) ? intval($in['id_plantacio']) : null;
  $data_inici = trim($in['data_inici'] ?? '');
  $id_varietat = isset($in['id_varietat']) ? intval($in['id_varietat']) : null;
  $quantitat = isset($in['quantitat']) ? floatval($in['quantitat']) : null;
  $unitat = trim($in['unitat'] ?? 'kg');
  
  // Camps opcionals
  $data_fi = !empty($in['data_fi']) ? trim($in['data_fi']) : null;
  $equip = isset($in['equip']) ? json_encode($in['equip']) : null; // Assumim que ve com array o string
  $observacions = trim($in['observacions'] ?? ''); // Això aniria a incidencies o un camp nou, l'esquema té 'incidencies'
  $incidencies = $observacions; 
  $condicions = isset($in['condicions']) ? json_encode($in['condicions']) : null;
  $estat_fenologic = trim($in['estat_fenologic'] ?? '');

  if (!$id_plantacio || !$data_inici || !$quantitat) {
    json_out(false, ['error' => 'Falten camps obligatoris (plantacio, data, quantitat).']);
  }

  $pdo = db();
  
  // Si no ve id_varietat, el busquem de la plantació
  if (!$id_varietat) {
      $stmt_v = $pdo->prepare("SELECT id_varietat FROM plantacions WHERE id_plantacio = ?");
      $stmt_v->execute([$id_plantacio]);
      $id_varietat = $stmt_v->fetchColumn();
      
      if (!$id_varietat) {
          json_out(false, ['error' => 'No s\'ha trobat la varietat per a aquesta plantació.']);
      }
  }
  
  // Generar Lot ID únic: L-{ANY}-{PLANTACIO}-{RANDOM}
  $any = date('Y', strtotime($data_inici));
  $lot_id = sprintf("L-%s-%d-%s", $any, $id_plantacio, substr(md5(uniqid()), 0, 6));

  $stmt = $pdo->prepare(
      "INSERT INTO collites (
          id_plantacio, data_inici, data_fi, id_varietat, quantitat_recoltada, unitat, 
          equip_recoltadors, condicions_ambientals, estat_fenologic, incidencies, lot_id
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
  );
  
  $stmt->execute([
      $id_plantacio, $data_inici, $data_fi, $id_varietat, $quantitat, $unitat,
      $equip, $condicions, $estat_fenologic, $incidencies, $lot_id
  ]);
  
  $id_collita = $pdo->lastInsertId();

  // Processar Treballadors (M:N)
  $treballadors = $in['treballadors'] ?? []; // Array d'IDs
  if (is_array($treballadors) && count($treballadors) > 0) {
      $stmt_trab = $pdo->prepare("INSERT INTO collites_treballadors (id_collita, id_treballador) VALUES (?, ?)");
      foreach ($treballadors as $id_treballador) {
          $stmt_trab->execute([$id_collita, $id_treballador]);
      }
  }
  
  // Crear entrada inicial a la taula de lots per traçabilitat
  $stmt_lot = $pdo->prepare(
      "INSERT INTO lots (id_lot, id_collita, data_creacio, origen_parcela)
       VALUES (?, ?, CURDATE(), (SELECT p.nom_parcela FROM parceles p JOIN plantacions pl ON pl.id_parcela = p.id_parcela WHERE pl.id_plantacio = ?))"
  );
  // Nota: La subquery per origen_parcela és una simplificació.
  $stmt_lot->execute([$lot_id, $id_collita, $id_plantacio]);

  json_out(true, ['id' => $id_collita, 'lot_id' => $lot_id]);
  
} catch (Throwable $e) {
  json_out(false, ['error' => $e->getMessage()]);
}
