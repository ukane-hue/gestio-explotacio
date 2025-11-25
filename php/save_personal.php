<?php
require_once __DIR__ . '/config.php';

// 1. VERIFICAR QUE L'USUARI ESTÀ LOGUEJAT
$user_id = verificar_login(); // Si no ho està, s'aturarà aquí.

try {
    $pdo = db();

    // 2. RECOLLIR DADES DEL FORMULARI
    $nom              = $_POST['nom'] ?? '';
    $nif              = $_POST['nif'] ?? '';
    $nss              = $_POST['nss'] ?? '';
    $telefon          = $_POST['telefon'] ?? '';
    $email            = $_POST['email'] ?? '';
    $carrec           = $_POST['carrec'] ?? '';
    $tipus_contracte  = $_POST['tipus_contracte'] ?? 'temporal';
    $durada_contracte = $_POST['durada_contracte'] ?? ''; // Data en format YYYY-MM-DD
    
    // Checkbox: Si està marcat és 1, sinó 0
    $te_carnet = isset($_POST['carnet']) ? 1 : 0;

    // 3. VALIDACIÓ BÀSICA
    if (empty($nom)) {
        json_out(false, ['missatge' => 'El nom és obligatori.']);
    }

    // Convertir data buida a NULL per evitar errors SQL
    if (empty($durada_contracte)) {
        $durada_contracte = null;
    }

    // 4. PREPARAR LA CONSULTA SQL (Amb user_id)
    $sql = "INSERT INTO personal 
            (nom, nif, nss, telefon, email, carrec, tipus_contracte, durada_contracte, te_carnet_fitosanitari, actiu, user_id) 
            VALUES 
            (:nom, :nif, :nss, :telefon, :email, :carrec, :tipus_contracte, :durada_contracte, :te_carnet, 1, :user_id)";

    $stmt = $pdo->prepare($sql);

    // 5. EXECUTAR
    $result = $stmt->execute([
        ':nom'              => $nom,
        ':nif'              => $nif,
        ':nss'              => $nss,
        ':telefon'          => $telefon,
        ':email'            => $email,
        ':carrec'           => $carrec,
        ':tipus_contracte'  => $tipus_contracte,
        ':durada_contracte' => $durada_contracte,
        ':te_carnet'        => $te_carnet,
        ':user_id'          => $user_id  // Important: Guardem qui ha creat el treballador
    ]);

    if ($result) {
        json_out(true, ['missatge' => 'Treballador guardat correctament!']);
    } else {
        json_out(false, ['missatge' => 'Error al guardar a la base de dades.']);
    }

} catch (Exception $e) {
    json_out(false, ['missatge' => 'Error del servidor: ' . $e->getMessage()]);
}
?>