<?php
require_once __DIR__ . '/config.php';

function test_request($file, $data = [], $method = 'POST') {
    $url = "http://localhost/web/gestio-explotacio/gestio-explotacio/php/$file";
    echo "Testing $file via CURL ($method)...\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookie.txt');
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    
    $output = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        return ['ok' => false, 'error' => 'Curl error: ' . curl_error($ch)];
    }
    curl_close($ch);
    
    if ($http_code != 200) {
        return ['ok' => false, 'error' => "HTTP $http_code", 'raw_output' => $output];
    }
    
    $decoded = json_decode($output, true);
    if ($decoded) return $decoded;
    
    return ['ok' => false, 'error' => 'JSON decode failed', 'raw_output' => $output];
}

echo "=== INICI TEST INTEGRAL ===\n";

// Netejar cookies anteriors
if (file_exists('/tmp/cookie.txt')) {
    unlink('/tmp/cookie.txt');
}

// 0. Test Autenticació
echo "--- Test Autenticació ---\n";
// 0.1 Verificar que no tenim accés inicialment
$resAuth = test_request('auth_check.php', [], 'GET');
if ($resAuth && isset($resAuth['error'])) {
    echo "✅ Accés denegat correctament sense sessió.\n";
} else {
    echo "⚠️ Hauria de denegar accés sense sessió.\n";
}

// 0.2 Login
$loginData = ['email' => 'admin@example.com', 'password' => 'admin123'];
$resLogin = test_request('login.php', $loginData);
if ($resLogin && $resLogin['ok']) {
    echo "✅ Login correcte.\n";
} else {
    echo "❌ Error al login.\n";
    print_r($resLogin);
    exit(1);
}

// 0.3 Verificar accés amb sessió
$resAuth2 = test_request('auth_check.php', [], 'GET');
if ($resAuth2 && $resAuth2['ok']) {
    echo "✅ Accés autoritzat amb sessió (Usuari: " . $resAuth2['user_name'] . ")\n";
} else {
    echo "❌ Error accedint amb sessió.\n";
    exit(1);
}
echo "-------------------------\n";

// 1. Crear Parcel·la
$parcelaData = [
    'nom' => 'Parcela Test ' . rand(1000,9999),
    'superficie' => 2.5,
    'cultiu' => 'Pomera',
    'varietat' => 'Golden',
    'geojson' => '{"type":"Polygon","coordinates":[[[0,0],[0,1],[1,1],[1,0],[0,0]]]}' // Fake geojson
];
$resParcela = test_request('save_parcela.php', $parcelaData);

if ($resParcela && $resParcela['ok']) {
    echo "✅ Parcel·la creada: ID " . $resParcela['id_parcela'] . ", Plantació ID " . $resParcela['id_plantacio'] . "\n";
    $id_plantacio = $resParcela['id_plantacio'];
} else {
    echo "❌ Error creant parcel·la\n";
    echo "Output Raw: " . print_r($resParcela, true) . "\n";
    echo "Errors: " . ($errors ?? '') . "\n";
    exit(1);
    exit(1);
}

// 1.1 Editar Parcel·la (UPDATE)
echo "Testing save_parcela.php (UPDATE) via CURL (POST)...\n";
$updateData = $parcelaData;
$updateData['id_parcela'] = $resParcela['id_parcela'];
$updateData['nom'] = $parcelaData['nom'] . " (EDITADA)";
$resUpdate = test_request('save_parcela.php', $updateData);

if ($resUpdate && $resUpdate['ok']) {
    echo "✅ Parcel·la actualitzada: ID " . $resUpdate['id_parcela'] . "\n";
} else {
    echo "❌ Error actualitzant parcel·la\n";
    print_r($resUpdate);
    exit(1);
}



// 1.2 Eliminar Parcel·la (Test)
echo "Testing delete_parcela.php via CURL (POST)...\n";
// Primer creem una parcel·la dummy per eliminar
$dummyData = $parcelaData;
$dummyData['nom'] = "Parcela Dummy Delete";
$resDummy = test_request('save_parcela.php', $dummyData);
if ($resDummy && $resDummy['ok']) {
    $id_dummy = $resDummy['id_parcela'];
    // Ara l'eliminem
    $resDel = test_request('delete_parcela.php', ['id_parcela' => $id_dummy]);
    if ($resDel && $resDel['ok']) {
        echo "✅ Parcel·la eliminada correctament (ID $id_dummy)\n";
    } else {
        echo "❌ Error eliminant parcel·la dummy\n";
        print_r($resDel);
        exit(1);
    }
} else {
    echo "❌ Error creant parcel·la dummy per test delete\n";
    exit(1);
}

// 1.5 Registrar Tractament (per verificar traçabilitat)
$tractamentData = [
    'id_plantacio' => $id_plantacio,
    'data' => date('Y-m-d', strtotime('-1 day')), // Ahir
    'observacions' => 'Mescla preventiva',
    'productes' => [
        ['nom' => 'Fungicida A', 'quantitat' => 2.5, 'unitat' => 'L'],
        ['nom' => 'Insecticida B', 'quantitat' => 0.5, 'unitat' => 'kg']
    ]
];
$resTract = test_request('save_tractament.php', $tractamentData);
if ($resTract && $resTract['ok']) {
    echo "✅ Tractament registrat: ID " . $resTract['id'] . "\n";
} else {
    echo "❌ Error registrant tractament\n";
    print_r($resTract);
    exit(1);
}

// 2. Registrar Collita
$collitaData = [
    'id_plantacio' => $id_plantacio,
    'data_inici' => date('Y-m-d'),
    'quantitat' => 1500.50,
    'unitat' => 'kg',
    'equip' => 'Equip A'
];
$resCollita = test_request('save_collita.php', $collitaData);

if ($resCollita && $resCollita['ok']) {
    echo "✅ Collita registrada: ID " . $resCollita['id'] . ", Lot " . $resCollita['lot_id'] . "\n";
    $id_collita = $resCollita['id'];
    $lot_id = $resCollita['lot_id'];
} else {
    echo "❌ Error registrant collita\n";
    print_r($resCollita);
    exit(1);
}

// ... (Qualitat) ...

// 5. Verificar Traçabilitat
$resTrace = test_request("get_tracabilitat.php?lot_id=$lot_id", [], 'GET');

if ($resTrace && $resTrace['ok']) {
    echo "✅ Traçabilitat obtinguda per lot $lot_id\n";
    $num_tract = count($resTrace['tractaments']);
    if ($num_tract >= 2) {
        echo "✅ Tractaments trobats: " . $num_tract . " (Correcte, s'esperaven almenys 2 productes)\n";
    } else {
        echo "⚠️ Tractaments trobats: " . $num_tract . " (S'esperaven 2)\n";
    }
} else {
    echo "❌ Error obtenint traçabilitat\n";
    print_r($resTrace);
    exit(1);
}




// 3. Control de Qualitat
$qualitatData = [
    'id_collita' => $id_collita,
    'calibre' => 75,
    'fermesa' => 6.5,
    'percentatge' => 95
];
$resQualitat = test_request('save_control_qualitat.php', $qualitatData);

if ($resQualitat && $resQualitat['ok']) {
    echo "✅ Qualitat registrada: ID " . $resQualitat['id'] . "\n";
} else {
    echo "❌ Error registrant qualitat\n";
    exit(1);
}

// 4. Verificar Informes
$resInforme = test_request('get_informe_collita.php', []);
if ($resInforme && $resInforme['ok']) {
    echo "✅ Informe obtingut. Dades:\n";
    print_r($resInforme['per_varietat']);
    
    if (isset($resInforme['recents'])) {
        echo "✅ Collites recents obtingudes: " . count($resInforme['recents']) . "\n";
    } else {
        echo "❌ No s'han rebut collites recents\n";
    }
} else {
    echo "❌ Error obtenint informe\n";
    exit(1);
}

// 1.8 Registrar Treballador (i Usuari)
echo "Testing save_treballador.php via CURL (POST)...\n";
$treballadorData = [
    'nom' => 'Joan',
    'cognom' => 'Pages',
    'dni' => '12345678A',
    'categoria' => 'Tractorista',
    'crear_usuari' => true,
    'email' => 'joan.pages@example.com',
    'password' => 'secret123',
    'rol' => 'gestor'
];
$resTreb = test_request('save_treballador.php', $treballadorData);
if ($resTreb && $resTreb['ok']) {
    $id_treballador = $resTreb['id'];
    echo "✅ Treballador registrat: ID $id_treballador (amb usuari)\n";
} else {
    echo "❌ Error registrant treballador\n";
    print_r($resTreb);
    // No sortim, seguim provant
}

// Intentar registrar duplicat
echo "Testing save_treballador.php via CURL (POST)...\n";
$resTrebDup = test_request('save_treballador.php', $treballadorData);
if (!$resTrebDup['ok']) {
    echo "✅ DNI duplicat detectat correctament.\n";
} else {
    echo "❌ Hauria d'haver fallat per DNI duplicat.\n";
}

// 2. Test Personal
echo "Testing save_treballador.php via CURL (POST)...\n";
$treballadorData = [
    'nom' => 'Joan',
    'cognom' => 'Pagès',
    'dni' => '12345678Z',
    'categoria' => 'Tractorista',
    'num_carnet_aplicador' => 'CARNET-001'
];
$resTrab = test_request('save_treballador.php', $treballadorData);
if ($resTrab && $resTrab['ok']) {
    echo "✅ Treballador registrat: ID " . $resTrab['id'] . "\n";
} else {
    echo "❌ Error registrant treballador\n";
    print_r($resTrab);
    exit(1);
}

// 2.1 Test Tasca
echo "Testing save_tasca.php via CURL (POST)...\n";
$tascaData = ['nom' => 'Podar', 'descripcio' => 'Poda d\'hivern'];
$resTasca = test_request('save_tasca.php', $tascaData);
if ($resTasca && $resTasca['ok']) {
    echo "✅ Tasca creada: ID " . $resTasca['id'] . "\n";
} else {
    echo "❌ Error creant tasca\n";
    exit(1);
}

// 2.2 Test Registre Treball
echo "Testing save_registre_treball.php via CURL (POST)...\n";
$registreData = [
    'id_treballador' => $resTrab['id'],
    'id_tasca' => $resTasca['id'],
    'id_parcela' => 1,
    'data' => date('Y-m-d'),
    'hora_inici' => '08:00',
    'hora_fi' => '13:00'
];
$resReg = test_request('save_registre_treball.php', $registreData);
if ($resReg && $resReg['ok']) {
    echo "✅ Registre de treball creat: ID " . $resReg['id'] . "\n";
} else {
    echo "❌ Error registrant treball\n";
    print_r($resReg);
    exit(1);
}
// 2.3 Test Certificació
echo "Testing save_certificacio.php via CURL (POST)...\n";
$certData = [
    'id_treballador' => $resTrab['id'],
    'tipus' => 'Carnet Fitosanitari',
    'data_caducitat' => date('Y-m-d', strtotime('+1 year'))
];
$resCert = test_request('save_certificacio.php', $certData);
if ($resCert && $resCert['ok']) {
    echo "✅ Certificació registrada: ID " . $resCert['id'] . "\n";
} else {
    echo "❌ Error registrant certificació\n";
    print_r($resCert);
    exit(1);
}

// 6. Test Observacions
echo "Testing save_observacio.php via CURL (POST)...\n";
$obsData = [
    'id_plantacio' => $id_plantacio,
    'plaga' => 'Pugó',
    'nivell' => 'mitja'
];
$resObs = test_request('save_observacio.php', $obsData);
if ($resObs && $resObs['ok']) {
    echo "✅ Observació registrada: ID " . $resObs['id'] . "\n";
} else {
    echo "❌ Error registrant observació\n";
    print_r($resObs);
    exit(1);
}

echo "=== TEST COMPLETAT AMB ÈXIT ===\n";
