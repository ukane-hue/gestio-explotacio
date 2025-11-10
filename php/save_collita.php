<?php
require_once __DIR__ . '/config.php';
try {
  $in = get_json_input();
  $data = $in['data'] ?? null;
  $varietat = trim($in['varietat'] ?? '');
  $quantitat = isset($in['quantitat']) ? floatval($in['quantitat']) : null;
  $equip = trim($in['equip'] ?? '');
  $observacions = trim($in['observacions'] ?? '');
  $parcel_id = isset($in['parcel_id']) && $in['parcel_id'] !== '' ? intval($in['parcel_id']) : null;

  if (!$data || $varietat === '') {
    json_out(false, ['error' => 'Falten camps obligatoris.']);
  }

  $pdo = db();
  $stmt = $pdo->prepare("INSERT INTO collites (parcel_id, data, varietat, quantitat, equip, observacions) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->execute([$parcel_id, $data, $varietat, $quantitat, $equip ?: null, $observacions ?: null]);
  json_out(true, ['id' => $pdo->lastInsertId()]);
} catch (Throwable $e) {
  json_out(false, ['error' => $e->getMessage()]);
}
