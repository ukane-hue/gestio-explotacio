<?php
// php/config.php
session_start(); // <--- AIXÒ ÉS EL MÉS IMPORTANT, INICIA LA SESSIÓ

$DB_HOST = 'localhost';
$DB_NAME = 'gestio_explotacio1';
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

function json_out($ok, $extra = []) {
  header('Content-Type: application/json');
  echo json_encode(array_merge(['ok' => $ok], $extra));
  exit;
}

// Funció nova per protegir les pàgines
function verificar_login() {
    if (!isset($_SESSION['user_id'])) {
        // Si no està loguejat, retornem error i parem
        json_out(false, ['missatge' => 'No has iniciat sessió', 'redirect' => 'login.html']);
    }
    return $_SESSION['user_id'];
}
?>