<?php
require_once __DIR__ . '/config.php';

try {
    $in = get_json_input();
    
    $nom = trim($in['nom'] ?? '');
    $cognom = trim($in['cognom'] ?? '');
    $dni = trim($in['dni'] ?? '');
    $crear_usuari = !empty($in['crear_usuari']); // Checkbox
    
    if (!$nom || !$cognom || !$dni) {
        json_out(false, ['error' => 'Nom, Cognom i DNI són obligatoris.']);
    }
    
    $pdo = db();
    
    // Verificar duplicats DNI (Treballador)
    $stmt_check = $pdo->prepare("SELECT id_treballador FROM treballadors WHERE dni = ?");
    $stmt_check->execute([$dni]);
    if ($stmt_check->fetch()) {
        json_out(false, ['error' => 'Aquest DNI ja està registrat com a treballador.']);
    }

    // Si es vol crear usuari, verificar duplicats Email (Usuari)
    if ($crear_usuari) {
        $email = trim($in['email'] ?? '');
        $password = $in['password'] ?? '';
        $rol = $in['rol'] ?? 'operari';

        if (!$email || !$password) {
            json_out(false, ['error' => 'Email i Contrasenya són obligatoris per crear un usuari.']);
        }

        $stmt_check_user = $pdo->prepare("SELECT id_usuari FROM usuaris WHERE email = ?");
        $stmt_check_user->execute([$email]);
        if ($stmt_check_user->fetch()) {
            json_out(false, ['error' => 'Aquest email ja està registrat com a usuari de sistema.']);
        }
    }
    
    $pdo->beginTransaction();

    // 1. Insertar Treballador
    $stmt = $pdo->prepare("
        INSERT INTO treballadors (nom, cognom, dni, telefon, email, adreca, tipus_contracte, categoria, num_carnet_aplicador, data_inici)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())
    ");
    
    $stmt->execute([
        $nom, 
        $cognom, 
        $dni, 
        $in['telefon'] ?? null, 
        $in['email'] ?? null, 
        $in['adreca'] ?? null, 
        $in['tipus_contracte'] ?? null, 
        $in['categoria'] ?? null, 
        $in['num_carnet_aplicador'] ?? null
    ]);
    
    $id_treballador = $pdo->lastInsertId();

    // 2. Insertar Usuari (si cal)
    if ($crear_usuari) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt_user = $pdo->prepare("
            INSERT INTO usuaris (nom, cognoms, email, contrasenya_hash, rol, telefon)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt_user->execute([
            $nom,
            $cognom,
            $email,
            $password_hash,
            $rol,
            $in['telefon'] ?? null
        ]);
        // Nota: No tenim un camp explícit 'id_usuari' a la taula treballadors per vincular, 
        // però comparteixen email i dades personals. Si calgués vincular-los fortament, caldria un camp FK.
        // Per ara, assumim que són entitats separades lògicament però creades juntes.
    }

    $pdo->commit();
    
    json_out(true, ['id' => $id_treballador, 'usuari_creat' => $crear_usuari]);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_out(false, ['error' => $e->getMessage()]);
}
