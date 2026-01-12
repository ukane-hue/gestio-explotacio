<?php
require_once __DIR__ . '/config.php';

try {
  $in = get_json_input();

  $id_parcela = $in['id_parcela'] ?? null; // Si ve, és UPDATE
  $nom = trim($in['nom'] ?? '');
  $superficie = floatval($in['superficie'] ?? 0);
  $cultiu = trim($in['cultiu'] ?? '');
  $varietat = trim($in['varietat'] ?? '');
  $geojson = $in['geojson'] ?? null;

  if ($nom === '' || $superficie <= 0 || $cultiu === '') {
    json_out(false, ['error' => 'Camps obligatoris buits.']);
  }

  // Calcular l'àrea a partir del GeoJSON (en m²)
  $area_m2 = calcular_area_m2_des_de_geojson($geojson);
  
  // Convertir GeoJSON a string per guardar-lo
  $geojson_str = is_array($geojson) ? json_encode($geojson) : $geojson;

  $pdo = db();
  
  // Obtenir usuari actual
  if (session_status() === PHP_SESSION_NONE) session_start();
  $id_usuari = $_SESSION['user_id'] ?? null;
  $rol = $_SESSION['user_role'] ?? '';

  if (!$id_usuari) {
      json_out(false, ['error' => 'No autenticat.']);
  }

  $pdo->beginTransaction();

  if ($id_parcela) {
      // UPDATE
      // Verificar propietat (si no és admin/gestor)
      if ($rol !== 'admin' && $rol !== 'gestor') {
          $stmt_check = $pdo->prepare("SELECT id_propietari FROM parceles WHERE id_parcela = ?");
          $stmt_check->execute([$id_parcela]);
          $owner = $stmt_check->fetchColumn();
          if ($owner != $id_usuari) {
              json_out(false, ['error' => 'No tens permís per editar aquesta parcel·la.']);
          }
      }

      $stmt = $pdo->prepare("
          UPDATE parceles 
          SET nom_parcela = ?, superficie = ?, perimetre_geo = ?
          WHERE id_parcela = ?
      ");
      $stmt->execute([$nom, $superficie, $geojson_str, $id_parcela]);
  } else {
      // INSERT
      $stmt = $pdo->prepare("
          INSERT INTO parceles (nom_parcela, superficie, perimetre_geo, id_propietari)
          VALUES (?, ?, ?, ?)
      ");
      $stmt->execute([$nom, $superficie, $geojson_str, $id_usuari]);
      $id_parcela = $pdo->lastInsertId();
  }

  // Gestió de Cultiu/Varietat (Plantació)
  // 1. Buscar/Crear Especie
  $stmt_esp = $pdo->prepare("SELECT id_especie FROM especies WHERE nom_comu = ?");
  $stmt_esp->execute([$cultiu]);
  $id_especie = $stmt_esp->fetchColumn();

  if (!$id_especie) {
      $stmt_new_esp = $pdo->prepare("INSERT INTO especies (nom_cientific, nom_comu) VALUES (?, ?)");
      $stmt_new_esp->execute([$cultiu, $cultiu]);
      $id_especie = $pdo->lastInsertId();
  }

  // 2. Buscar/Crear Varietat
  $id_varietat = null;
  if ($varietat !== '') {
      $stmt_var = $pdo->prepare("SELECT id_varietat FROM varietats WHERE nom_varietat = ? AND id_especie = ?");
      $stmt_var->execute([$varietat, $id_especie]);
      $id_varietat = $stmt_var->fetchColumn();

      if (!$id_varietat) {
          $stmt_new_var = $pdo->prepare("INSERT INTO varietats (id_especie, nom_varietat) VALUES (?, ?)");
          $stmt_new_var->execute([$id_especie, $varietat]);
          $id_varietat = $pdo->lastInsertId();
      }
  } else {
      // Generica
      $stmt_var = $pdo->prepare("SELECT id_varietat FROM varietats WHERE nom_varietat = 'Generica' AND id_especie = ?");
      $stmt_var->execute([$id_especie]);
      $id_varietat = $stmt_var->fetchColumn();
      
      if (!$id_varietat) {
           $stmt_new_var = $pdo->prepare("INSERT INTO varietats (id_especie, nom_varietat) VALUES (?, 'Generica')");
           $stmt_new_var->execute([$id_especie]);
           $id_varietat = $pdo->lastInsertId();
      }
  }

  // 3. Actualitzar o Crear Plantació
  // Busquem si ja té plantació
  $stmt_check_plant = $pdo->prepare("SELECT id_plantacio FROM plantacions WHERE id_parcela = ?");
  $stmt_check_plant->execute([$id_parcela]);
  $id_plantacio = $stmt_check_plant->fetchColumn();

  if ($id_plantacio) {
      // Actualitzem la varietat de la plantació existent
      $stmt_update_plant = $pdo->prepare("UPDATE plantacions SET id_varietat = ? WHERE id_plantacio = ?");
      $stmt_update_plant->execute([$id_varietat, $id_plantacio]);
  } else {
      // Creem nova plantació
      $stmt_plant = $pdo->prepare(
          "INSERT INTO plantacions (id_parcela, id_varietat, data_plantacio, nombre_arbres)
           VALUES (?, ?, CURDATE(), 0)"
      );
      $stmt_plant->execute([$id_parcela, $id_varietat]);
      $id_plantacio = $pdo->lastInsertId();
  }

  $pdo->commit();

  json_out(true, [
    'id_parcela' => $id_parcela,
    'id_plantacio' => $id_plantacio,
    'area_m2' => $area_m2 ? round($area_m2, 2) : null
  ]);

} catch (Throwable $e) {
  if (isset($pdo) && $pdo->inTransaction()) {
      $pdo->rollBack();
  }
  json_out(false, ['error' => $e->getMessage()]);
}
