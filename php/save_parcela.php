<?php
require_once __DIR__ . '/config.php';
try {
  $in = get_json_input();
  $nom = trim($in['nom'] ?? '');
  $superficie = floatval($in['superficie'] ?? 0);
  $cultiu = trim($in['cultiu'] ?? '');
  $varietat = trim($in['varietat'] ?? '');
  $geojson = $in['geojson'] ?? null;

  if ($nom === '' || $superficie <= 0 || $cultiu === '') {
    json_out(false, ['error' => 'Camps obligatoris buits.']);
  }

  $pdo = db();
  $stmt = $pdo->prepare("INSERT INTO parceles (nom, superficie, cultiu, varietat, geojson) VALUES (?, ?, ?, ?, ?)");
  $stmt->execute([$nom, $superficie, $cultiu, $varietat ?: null, $geojson]);
  json_out(true, ['id' => $pdo->lastInsertId()]);
} catch (Throwable $e) {
  json_out(false, ['error' => $e->getMessage()]);
}
