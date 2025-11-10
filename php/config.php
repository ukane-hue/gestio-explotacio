<?php
// Configuració de la base de dades (canvia-ho segons el teu entorn)
$DB_HOST = 'localhost';
$DB_NAME = 'gestio_explotacio';
$DB_USER = 'root';
$DB_PASS = '';

$DSN = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
$OPT = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

function db() {
  static $pdo = null;
  global $DSN, $DB_USER, $DB_PASS, $OPT;
  if ($pdo === null) {
    $pdo = new PDO($DSN, $DB_USER, $DB_PASS, $OPT);
  }
  return $pdo;
}

function get_json_input() {
  $input = file_get_contents('php://input');
  if ($input) {
    $data = json_decode($input, true);
    if (json_last_error() === JSON_ERROR_NONE) return $data;
  }
  // Fallback a $_POST
  if (!empty($_POST)) return $_POST;
  return [];
}

function json_out($ok, $extra = []) {
  header('Content-Type: application/json');
  echo json_encode(array_merge(['ok' => $ok], $extra));
  exit;
}
