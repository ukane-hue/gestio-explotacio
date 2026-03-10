<?php
// Configuració de la base de dades (canvia-ho segons el teu entorn)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

/**
 * Calcula l'àrea en m² d'un polígon definit en GeoJSON.
 * Accepta FeatureCollection, Feature o directament una geometria Polygon.
 * Retorna null si no pot calcular l'àrea.
 */
function calcular_area_m2_des_de_geojson($geojson) {
  if (!$geojson) return null;

  // Si ve com a array PHP, el passem a JSON per assegurar format
  if (is_array($geojson)) {
    $geojson = json_encode($geojson);
  }

  $data = json_decode($geojson, true);
  if (!$data || !isset($data['type'])) {
    return null;
  }

  // Obtenim la geometria
  $geom = null;
  if ($data['type'] === 'FeatureCollection') {
    if (empty($data['features']) || !isset($data['features'][0]['geometry'])) {
      return null;
    }
    $geom = $data['features'][0]['geometry'];
  } elseif ($data['type'] === 'Feature') {
    if (!isset($data['geometry'])) return null;
    $geom = $data['geometry'];
  } else {
    // Potser ja és la geometria
    $geom = $data;
  }

  if (!$geom || !isset($geom['type']) || $geom['type'] !== 'Polygon') {
    return null;
  }

  if (!isset($geom['coordinates']) || !is_array($geom['coordinates']) || empty($geom['coordinates'][0])) {
    return null;
  }

  // Primer anell (exterior)
  $ring = $geom['coordinates'][0];

  // Com a mínim 3 punts
  if (count($ring) < 3) {
    return null;
  }

  // Convertim lon/lat (graus) a metres
  $lats = array_map(function($p) { return $p[1]; }, $ring);
  $meanLat = array_sum($lats) / max(count($lats), 1);

  $latFactor = 111320.0; // metres per grau de latitud
  $lonFactor = $latFactor * cos(deg2rad($meanLat)); // metres per grau de longitud

  $xy = [];
  foreach ($ring as $pt) {
    // Esperem [lon, lat]
    if (!is_array($pt) || count($pt) < 2) continue;
    $lon = $pt[0];
    $lat = $pt[1];
    $x = $lon * $lonFactor;
    $y = $lat * $latFactor;
    $xy[] = [$x, $y];
  }

  if (count($xy) < 3) {
    return null;
  }

  // Fórmula del "shoelace"
  $area = 0.0;
  $n = count($xy);
  for ($i = 0; $i < $n; $i++) {
    $j = ($i + 1) % $n;
    $area += $xy[$i][0] * $xy[$j][1] - $xy[$j][0] * $xy[$i][1];
  }

  $area = abs($area) / 2.0; // en m²
  return $area;
}

function json_out($ok, $extra = []) {
  header('Content-Type: application/json');
  echo json_encode(array_merge(['ok' => $ok, 'success' => $ok], $extra));
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
