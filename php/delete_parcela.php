<?php
require_once __DIR__ . '/config.php';

try {
    $in = get_json_input();
    $id_parcela = $in['id_parcela'] ?? null;

    if (!$id_parcela) {
        json_out(false, ['error' => 'ID de parcel·la no proporcionat.']);
    }

    $pdo = db();
    
    // Obtenir usuari actual
    if (session_status() === PHP_SESSION_NONE) session_start();
    $id_usuari = $_SESSION['user_id'] ?? null;
    $rol = $_SESSION['user_role'] ?? '';

    if (!$id_usuari) {
        json_out(false, ['error' => 'No autenticat.']);
    }

    // Verificar propietat (si no és admin/gestor)
    if ($rol !== 'admin' && $rol !== 'gestor') {
        $stmt_check = $pdo->prepare("SELECT id_propietari FROM parceles WHERE id_parcela = ?");
        $stmt_check->execute([$id_parcela]);
        $owner = $stmt_check->fetchColumn();
        if ($owner != $id_usuari) {
            json_out(false, ['error' => 'No tens permís per eliminar aquesta parcel·la.']);
        }
    }

    // 1. Comprovar Integritat Referencial
    // Comprovem si hi ha plantacions, i si aquestes tenen collites, tractaments, etc.
    // També comprovem registre_treball directament lligat a la parcel·la.

    // Comprovar registre_treball
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registre_treball WHERE id_parcela = ?");
    $stmt->execute([$id_parcela]);
    if ($stmt->fetchColumn() > 0) {
        json_out(false, ['error' => 'No es pot eliminar: Hi ha registres de treball associats a aquesta parcel·la.']);
    }

    // Comprovar plantacions i les seves dependències
    $stmt = $pdo->prepare("SELECT id_plantacio FROM plantacions WHERE id_parcela = ?");
    $stmt->execute([$id_parcela]);
    $plantacions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($plantacions)) {
        $ids_plantacio_str = implode(',', $plantacions);
        
        // Comprovar Collites
        $stmt = $pdo->query("SELECT COUNT(*) FROM collites WHERE id_plantacio IN ($ids_plantacio_str)");
        if ($stmt->fetchColumn() > 0) {
            json_out(false, ['error' => 'No es pot eliminar: Hi ha collites registrades en aquesta parcel·la.']);
        }

        // Comprovar Tractaments
        $stmt = $pdo->query("SELECT COUNT(*) FROM tractaments WHERE id_plantacio IN ($ids_plantacio_str)");
        if ($stmt->fetchColumn() > 0) {
            json_out(false, ['error' => 'No es pot eliminar: Hi ha tractaments registrats en aquesta parcel·la.']);
        }
        
        // Comprovar Observacions
        $stmt = $pdo->query("SELECT COUNT(*) FROM observacions_fitosanitaries WHERE id_plantacio IN ($ids_plantacio_str)");
        if ($stmt->fetchColumn() > 0) {
            json_out(false, ['error' => 'No es pot eliminar: Hi ha observacions registrades en aquesta parcel·la.']);
        }
    }

    // Si passem totes les comprovacions, podem eliminar.
    // L'esquema té ON DELETE SET NULL o CASCADE segons el cas, però aquí volem ser estrictes o netejar nosaltres.
    // Si eliminem la parcel·la:
    // - plantacions: ON DELETE SET NULL (es quedaran orfes de parcel·la, però existiran). 
    //   Això pot no ser el desitjat si volem netejar tot. 
    //   Si l'usuari vol eliminar la parcel·la, probablement vol eliminar les plantacions associades si no tenen activitat.
    //   Però com que ja hem comprovat que no tenen activitat (collites/tractaments), podem eliminar les plantacions també.

    $pdo->beginTransaction();

    // Eliminar plantacions associades (ja hem verificat que no tenen dades crítiques)
    if (!empty($plantacions)) {
        // Primer eliminem previsions, trampes, etc. si n'hi hagués (assumim que no són crítiques o esborrem en cascada)
        // Per simplificar i seguretat, esborrem les plantacions manualment.
        $pdo->exec("DELETE FROM plantacions WHERE id_parcela = $id_parcela");
    }
    
    // Eliminar sectors si n'hi ha (ON DELETE CASCADE ja definit a schema, però per si de cas)
    // $pdo->exec("DELETE FROM sectors WHERE id_parcela = $id_parcela");

    // Eliminar la parcel·la
    $stmt = $pdo->prepare("DELETE FROM parceles WHERE id_parcela = ?");
    $stmt->execute([$id_parcela]);

    $pdo->commit();

    json_out(true, ['missatge' => 'Parcel·la eliminada correctament.']);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_out(false, ['error' => $e->getMessage()]);
}
