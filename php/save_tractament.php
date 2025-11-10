<?php
require_once __DIR__ . '/config.php';
try {
  $in = get_json_input();
  $data = $in['data'] ?? null;
  $producte = trim($in['producte'] ?? '');
  $quantitat = isset($in['quantitat']) ? floatval($in['quantitat']) : null;
  $parcel_id = isset($in['parcel_id']) && $in['parcel_id'] !== '' ? intval($in['parcel_id']) : null;
  $observacions = trim($in['observacions'] ?? '');

  if (!$data || $producte === '') {
    json_out(false, ['error' => 'Falten camps obligatoris.']);
  }

  $pdo = db();
  $stmt = $pdo->prepare("INSERT INTO tractaments (parcel_id, data, producte, quantitat, observacions) VALUES (?, ?, ?, ?, ?)");
  $stmt->execute([$parcel_id, $data, $producte, $quantitat, $observacions ?: null]);
  json_out(true, ['id' => $pdo->lastInsertId()]);
} catch (Throwable $e) {
  json_out(false, ['error' => $e->getMessage()]);
}
